describe('View.Fields.Base.RecurrenceField', function() {
    var app, field, createFieldProperties, sandbox,
        module = 'Meetings';

    beforeEach(function() {
        app = SugarTest.app;
        sandbox = sinon.sandbox.create();
        createFieldProperties = {
            client: 'base',
            name: 'recurrence',
            type: 'recurrence',
            viewName: 'edit',
            module: module
        };
        field = SugarTest.createField(createFieldProperties);
    });

    afterEach(function() {
        sandbox.restore();
        if (field) {
            field.dispose();
        }
        app.cache.cutAll();
        app.view.reset();
    });

    describe('Render', function() {
        var fieldVisibility;

        beforeEach(function() {
            fieldVisibility = {};
            sandbox.stub(field, '_showField', function(fieldName) {
                fieldVisibility[fieldName] = 'shown';
            });
            sandbox.stub(field, '_hideField', function(fieldName) {
                fieldVisibility[fieldName] = 'hidden';
            });
        });

        it('should show repeat day of week field when repeat type is weekly', function() {
            field.model.set('repeat_type', '');
            field.$el.wrap('<div class="record-cell" data-type="recurrence"><div>');
            field.render();
            expect(field.$el.closest('.record-cell')).not.toBeVisible();
        });

        it('should show recurrence field when repeat type is Daily', function() {
            field.model.set('repeat_type', 'Daily');
            field.$el.wrap('<div class="record-cell" data-type="recurrence"><div>');
            field.render();
            expect(field.$el.closest('.record-cell')).not.toBeVisible();
        });

        it('should show repeat day of week field when repeat type is weekly', function() {
            field.model.set('repeat_type', 'Weekly');
            expect(fieldVisibility.repeat_dow).toEqual('shown');
        });

        it('should hide repeat day of week field when repeat type is not weekly', function() {
            field.model.set('repeat_type', 'Daily');
            expect(fieldVisibility.repeat_dow).toEqual('hidden');
        });

        it('should show both repeat_count and repeat_until when in edit mode', function() {
            field.action = 'edit';
            field.render();
            expect(fieldVisibility.repeat_count).toEqual('shown');
            expect(fieldVisibility.repeat_until).toEqual('shown');
        });

        it('should show repeat_until and hide repeat_count when in detail mode and repeat_until has a value', function() {
            field.action = 'detail';
            field.model.set('repeat_until', 'foo');
            field.render();
            expect(fieldVisibility.repeat_count).toEqual('hidden');
            expect(fieldVisibility.repeat_until).toEqual('shown');
        });

        it('should show repeat_count and hide repeat_until when in detail mode and repeat_until does not have a value', function() {
            field.action = 'detail';
            field.model.set('repeat_count', 'bar');
            field.render();
            expect(fieldVisibility.repeat_count).toEqual('shown');
            expect(fieldVisibility.repeat_until).toEqual('hidden');
        });
    });

    describe('Defaulting Fields', function() {
        it('should set fields to defaults when repeat type changes and field is blank', function() {
            var expected = {
                repeat_type: 'Weekly',
                repeat_interval: 1,
                repeat_count: 10,
                repeat_until: ''
            };

            field.fields = [
                { name: 'repeat_interval', def: { 'default': expected.repeat_interval } },
                { name: 'repeat_count', def: { 'default': expected.repeat_count } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_interval: null,
                repeat_count: undefined
            });
            field.model.set('repeat_type', 'Weekly');
            expect(field.model.attributes).toEqual(expected);
        });

        it('should not set fields to defaults when repeat type changes and field is not blank', function() {
            var expected = {
                repeat_type: 'Weekly',
                repeat_interval: 2,
                repeat_count: 11,
                repeat_until: ''
            };

            field.fields = [
                { name: 'repeat_interval', def: { 'default': 1 } },
                { name: 'repeat_count', def: { 'default': 10 } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_interval: expected.repeat_interval,
                repeat_count: expected.repeat_count
            });
            field.model.set('repeat_type', 'Weekly');
            expect(field.model.attributes).toEqual(expected);
        });

        it('should set fields to defaults when repeat type changes to be non-repeating', function() {
            var expected = {
                repeat_type: '',
                repeat_interval: 1,
                repeat_count: 10,
                repeat_until: ''
            };

            field.fields = [
                { name: 'repeat_interval', def: { 'default': expected.repeat_interval } },
                { name: 'repeat_count', def: { 'default': expected.repeat_count } }
            ];

            field.model.set('repeat_type', 'Daily');
            field.model.set({
                repeat_interval: null,
                repeat_count: undefined,
                repeat_until: 'foo'
            });
            field.model.set('repeat_type', '');
            expect(field.model.attributes).toEqual(expected);
        });
    });

    describe('Toggle Repeat Count & Repeat Until values', function() {
        it('should clear repeat_until when repeat_count value is set', function() {
            field.model.set('repeat_until', '1/1/2015');
            field.model.set('repeat_count', 1);
            expect(field.model.get('repeat_until')).toEqual('');
        });

        it('should clear repeat_count when repeat_until value is set', function() {
            field.model.set('repeat_count', 1);
            field.model.set('repeat_until', '1/1/2015');
            expect(field.model.get('repeat_count')).toEqual('');
        });
    });

    describe('validating the fields', function() {
        var errors;

        beforeEach(function() {
            errors = {};
        });

        describe('is valid', function() {
            using('empty values', ['', null, undefined], function(value) {
                it('should allow repeat_count and repeat_until to be blank for non-recurring events', function() {
                    field.model.set('repeat_type', '', {silent: true});
                    field.model.set('repeat_count', value, {silent: true});
                    field.model.set('repeat_until', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(_.size(errors)).toBe(0);
                });
            });

            using('empty values', ['', null, undefined], function(value) {
                it('should allow repeat_count that is the minimum value', function() {
                    field.model.set('repeat_type', 'Daily', {silent: true});
                    field.model.set('repeat_count', field.repeatCountMin, {silent: true});
                    field.model.set('repeat_until', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(_.size(errors)).toBe(0);
                });
            });

            using('empty values', ['', null, undefined], function(value) {
                it('should allow repeat_count that is greater than the minimum value', function() {
                    // always more than the minimum, even if the minimum changes
                    var repeatCount = field.repeatCountMin + 1;

                    field.model.set('repeat_type', 'Daily', {silent: true});
                    field.model.set('repeat_count', repeatCount, {silent: true});
                    field.model.set('repeat_until', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(_.size(errors)).toBe(0);
                });
            });

            using('empty values', [0, '', null, undefined], function(value) {
                it('should allow repeat_count to be empty when repeat_until is not empty', function() {
                    field.model.set('repeat_type', 'Daily', {silent: true});
                    field.model.set('repeat_count', value, {silent: true});
                    field.model.set('repeat_until', '1/1/2015', {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(_.size(errors)).toBe(0);
                });
            });
        });

        describe('is invalid', function() {
            using('empty values', ['', null, undefined], function(value) {
                it('should not allow repeat_count and repeat_until to be blank for recurring events', function() {
                    field.model.set('repeat_type', 'Daily', {silent: true});
                    field.model.set('repeat_count', value, {silent: true});
                    field.model.set('repeat_until', value, {silent: true});

                    field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                    expect(errors.repeat_count).toEqual({required: true});
                });
            });

            it('should now allow repeat_count that is less than the minimum value', function() {
                // always less than the minimum, even if the minimum changes
                var repeatCount = field.repeatCountMin - 1;

                field.model.set('repeat_type', 'Daily', {silent: true});
                field.model.set('repeat_count', repeatCount, {silent: true});
                field.model.set('repeat_until', '', {silent: true});

                field._doValidateRepeatCountOrUntilRequired(null, errors, $.noop);

                expect(errors.repeat_count).toEqual({minValue: field.repeatCountMin});
            });
        });
    });
});
