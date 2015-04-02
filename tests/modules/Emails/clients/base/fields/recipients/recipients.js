describe("Emails.fields.recipients", function() {
    var app,
        field,
        context,
        model,
        dataProvider;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('recipients', 'field', 'base', 'edit', 'Emails');
        SugarTest.loadHandlebarsTemplate('recipients', 'field', 'base', 'select2-selection', 'Emails');
        SugarTest.loadPlugin('Tooltip');
        SugarTest.testMetadata.set();

        context = app.context.getContext({
            module: "Emails"
        });
        context.prepare();
        model = context.get('model');
        field = SugarTest.createField('base', 'recipients', 'recipients', 'edit', undefined, context.get('module'), model, context, true);
    });

    afterEach(function() {
        field.dispose();
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        delete app.plugins.plugins['field']['Tooltip'];
    });

    describe('manipulating the value of the field', function() {
        it('should add recipients to the collection', function() {
            var recipients = [
                {email: 'will@example.com', name: 'Will Westin'},
                {email: 'sarah@example.com', name: 'Sarah Smith'},
                {email: 'sally@example.com', name: 'Sally Bronsen'}
            ];
            field.render();
            // make sure the collection is empty
            expect(field.model.get(field.name).length).toBe(0);
            expect(field.getFieldElement().select2('data').length).toBe(0);
            // now add the recipients
            field.model.get(field.name).add(recipients);
            // verify that the field has the correct number of recipients
            expect(field.model.get(field.name).length).toBe(recipients.length);
            // verify that the DOM has been updated accordingly
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
        });
        it('should remove recipients from the collection', function() {
            var recipients = [
                {email: 'will@example.com', name: 'Will Westin'},
                {email: 'sarah@example.com', name: 'Sarah Smith'},
                {email: 'sally@example.com', name: 'Sally Bronsen'}
            ];
            field.render();
            // seed the field with a few recipients
            field.model.get(field.name).add(recipients);
            expect(field.model.get(field.name).length).toBe(recipients.length);
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
            // now remove the recipients
            var recipientsToRemove = [
                    field.model.get(field.name).at(0),
                    field.model.get(field.name).at(2)
                ],
                expected           = recipients.length - recipientsToRemove.length;
            field.model.get(field.name).remove(recipientsToRemove);
            // verify that the field has the correct number of recipients
            expect(field.model.get(field.name).length).toBe(expected);
            // verify that the DOM has been updated accordingly
            expect(field.getFieldElement().select2('data').length).toBe(expected);
        });
        it('should reset the collection to be empty', function() {
            var recipients = [
                {email: 'will@example.com', name: 'Will Westin'},
                {email: 'sarah@example.com', name: 'Sarah Smith'},
                {email: 'sally@example.com', name: 'Sally Bronsen'}
            ];
            field.render();
            // seed the field with a few recipients
            field.model.get(field.name).add(recipients);
            expect(field.model.get(field.name).length).toBe(recipients.length);
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
            // now reset the collection
            field.model.get(field.name).reset();
            // verify that the field has the correct number of recipients
            expect(field.model.get(field.name).length).toBe(0);
            // verify that the DOM has been updated accordingly
            expect(field.getFieldElement().select2('data').length).toBe(0);
        });
        it('should reset the collection with a new set of recipients', function() {
            var recipients = [
                {email: 'will@example.com', name: 'Will Westin'},
                {email: 'sarah@example.com', name: 'Sarah Smith'},
                {email: 'sally@example.com', name: 'Sally Bronsen'}
            ];
            field.render();
            // seed the field with a few recipients
            field.model.get(field.name).add(recipients);
            expect(field.model.get(field.name).length).toBe(recipients.length);
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
            // now reset the collection
            recipients = [
                field.model.get(field.name).at(0),
                field.model.get(field.name).at(2)
            ];
            field.model.get(field.name).reset(recipients);
            // verify that the field has the correct number of recipients
            expect(field.model.get(field.name).length).toBe(recipients.length);
            // verify that the DOM has been updated accordingly
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
        });
        it('should set the value of the field to a new collection', function() {
            var recipients = [
                {email: 'will@example.com', name: 'Will Westin'},
                {email: 'sarah@example.com', name: 'Sarah Smith'},
                {email: 'sally@example.com', name: 'Sally Bronsen'}
            ];
            field.render();
            // seed the field with a few recipients
            field.model.get(field.name).add(recipients);
            expect(field.model.get(field.name).length).toBe(recipients.length);
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
            // now set the value
            recipients = [
                field.model.get(field.name).at(0),
                field.model.get(field.name).at(2)
            ];
            field.model.set(field.name, new Backbone.Collection(recipients));
            // verify that the field has the correct number of recipients
            expect(field.model.get(field.name).length).toBe(recipients.length);
            // verify that the DOM has been updated accordingly
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
        });
        it('should set the value of the field to a new collection even when the new value is not a collection', function() {
            var recipients = [
                {email: 'will@example.com', name: 'Will Westin'},
                {email: 'sarah@example.com', name: 'Sarah Smith'},
                {email: 'sally@example.com', name: 'Sally Bronsen'}
            ];
            field.render();
            // seed the field with a few recipients
            field.model.get(field.name).add(recipients);
            expect(field.model.get(field.name).length).toBe(recipients.length);
            expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
            // now set the value
            field.model.set(field.name, recipients[2]);
            // verify that the field has the correct number of recipients
            expect(field.model.get(field.name).length).toBe(1);
            // verify that the DOM has been updated accordingly
            expect(field.getFieldElement().select2('data').length).toBe(1);
        });
    });

    describe('interacting with Select2', function() {
        describe('search for more recipients', function() {
            var query,
                apiSearchStub;

            beforeEach(function() {
                jasmine.Clock.useMock();
                query = {callback: sinon.stub()};
            });

            afterEach(function() {
                delete query;
                apiSearchStub.restore();
            });

            it("Should call the query callback with one record when the api call is successful and returns one record.", function() {
                var records = [{email: "will@example.com", name: "Will Westin"}];

                apiSearchStub = sinon.stub(app.api, "call", function(method, url, data, callbacks) {
                    callbacks.success({records: records});
                    callbacks.complete();
                });

                field.loadOptions(query);
                jasmine.Clock.tick(301);

                var actual = query.callback.lastCall.args[0].results.length;
                expect(actual).toBe(records.length);
            });

            it("Should call the query callback with no records when the api call results in an error.", function() {
                apiSearchStub = sinon.stub(app.api, "call", function(method, url, data, callbacks) {
                    callbacks.error();
                    callbacks.complete();
                });

                field.loadOptions(query);
                jasmine.Clock.tick(301);

                var actual = query.callback.lastCall.args[0].results.length;
                expect(actual).toBe(0);
            });

            it("Should make a call to the Mail API.", function() {
                apiSearchStub = sinon.stub(app.api, "call");

                field.loadOptions(query);
                jasmine.Clock.tick(301);

                var actual = apiSearchStub.callCount;
                expect(actual).toBe(1);
            });
        });

        describe('create a new recipient option for the user to select', function() {
            it("Should return undefined when data is not empty.", function() {
                var data   = [{id: "foo", email: "foo@bar.com"}],
                    actual = field.createOption("foo", data);

                expect(actual).toBeUndefined();
            });

            it("Should return a new option as an object when data is empty.", function() {
                var data     = [],
                    expected = {id: "foo@bar.com", email: "foo@bar.com"},
                    actual   = field.createOption(expected.email, data);

                expect(actual).toEqual(expected);
            });
        });

        describe('format the selected recipients', function() {
            it("Should return the recipient's name when it exists.", function() {
                var recipient = {email: "will@example.com", name: "Will Westin"},
                    actual    = $(field.formatSelection(recipient)).text();

                expect(actual).toEqual(recipient.name);
            });

            it("Should return the recipient's email address when name doesn't exist.", function() {
                var recipient = {email: "will@example.com"},
                    actual    = $(field.formatSelection(recipient)).text();

                expect(actual).toEqual(recipient.email);
            });
            it("Should return the selection by wrapping the tooltip elements", function() {
                var recipient = {email: "will@example.com"},
                    actualPlugin = $(field.formatSelection(recipient)).attr('rel'),
                    actualTitle = $(field.formatSelection(recipient)).data('title');

                expect(actualPlugin).toBe('tooltip');
                expect(actualTitle).toBe(recipient.email);
            });
        });

        describe('format options the user can select', function() {
            it("Should return the recipient's name and email address when they both exist.", function() {
                var recipient = {email: "will@example.com", name: "Will Westin"},
                    actual    = field.formatResult(recipient);

                expect(actual).toEqual('"Will Westin" &lt;will@example.com&gt;');
            });

            it("Should return the recipient's email address when name doesn't exist.", function() {
                var recipient = {email: "will@example.com"},
                    actual    = field.formatResult(recipient);

                expect(actual).toEqual(recipient.email);
            });
        });

        describe('respond when the user selects an option from the list', function() {
            it("Should return false when event.object does not exist.", function() {
                var event  = {},
                    actual = field._handleEventOnSelected(event);
                expect(actual).toBeFalsy();
            });

            it("Should return true when event.object exists and id and email are not equal.", function() {
                var recipient = {id: "abcd", email: "foo@bar.com"},
                    event     = {object: recipient}
                actual    = field._handleEventOnSelected(event);
                expect(actual).toBeTruthy();
            });

            describe('unknown email addresses must be validated', function() {
                var validateEmailAddressStub;

                beforeEach(function() {
                    validateEmailAddressStub = sinon.stub(field, "_validateEmailAddress");
                });

                afterEach(function() {
                    validateEmailAddressStub.restore();
                });

                it("Should return true when event.object exists and id and email are equal and the email address is valid.", function() {
                    validateEmailAddressStub.returns(true);

                    var recipient = {id: "foo@bar.com", email: "foo@bar.com"},
                        event     = {object: recipient},
                        actual    = field._handleEventOnSelected(event);
                    expect(actual).toBeTruthy();
                });

                it("Should return false when event.object exists and id and email are equal and the email address is invalid.", function() {
                    validateEmailAddressStub.returns(false);

                    var recipient = {id: "foo@bar.com", email: "foo@bar.com"},
                        event     = {object: recipient},
                        actual    = field._handleEventOnSelected(event);
                    expect(actual).toBeFalsy();
                });
            });
        });

        describe('synchronizing the collection on Select2 DOM changes', function() {
            it('should synchronize the collection with the data in Select2', function() {
                var recipients = [
                    {id: 'will@example.com', email: 'will@example.com'},
                    {id: 'sarah@example.com', email: 'sarah@example.com'},
                    {id: 'sally@example.com', email: 'sally@example.com'}
                ];
                field.render();
                // make sure the collection is empty
                expect(field.model.get(field.name).length).toBe(0);
                expect(field.getFieldElement().select2('data').length).toBe(0);
                // now add the recipients via Select2 and trigger a change event
                field.getFieldElement().select2('data', recipients).trigger('change');
                // verify that the field has the correct number of recipients
                expect(field.model.get(field.name).length).toBe(recipients.length);
                // verify that the DOM has been updated accordingly
                expect(field.getFieldElement().select2('data').length).toBe(recipients.length);
            });
        });

        describe('recipient field pills should reflect locked state', function() {
            afterEach(function() {
                field.def.readonly = false;
            });
            it('should be locked if field is readonly', function() {
                field.def.readonly = true;
                var recipient = new Backbone.Model({module: 'Contacts', name: 'Will Westin', email: 'will@example.com'});
                var actual = field._formatRecipient(recipient);
                var expected = {id: 'will@example.com', module: 'Contacts', email: 'will@example.com', locked: true, name: 'Will Westin'};
                expect(actual).toEqual(expected);
            });

            it('should be unlocked if field is not readonly', function() {
                var recipient = new Backbone.Model({module: 'Contacts', name: 'Will Westin', email: 'will@example.com'});
                var actual = field._formatRecipient(recipient);
                var expected = {id: 'will@example.com', module: 'Contacts', email: 'will@example.com', locked: false, name: 'Will Westin'};
                expect(actual).toEqual(expected);
            });
        });
    });

    describe('format recipients to get a consistent object to work with', function() {
        dataProvider = [
            {
                message:    "Should return an array of one recipient when the parameter is a Backbone model.",
                recipients: new Backbone.Model({email: "will@example.com", name: "Will Westin"}),
                expected:   1
            },
            {
                message:    "Should return an array of one recipient when the parameter is a standard object.",
                recipients: {email: "will@example.com", name: "Will Westin"},
                expected:   1
            },
            {
                message:    "Should return an array of one recipient when the parameter is a Backbone collection containing one model.",
                recipients: new Backbone.Collection([{email: "will@example.com", name: "Will Westin"}]),
                expected:   1
            },
            {
                message:    "Should return an array of three recipients when the parameter is a Backbone collection containing three models.",
                recipients: new Backbone.Collection([
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ]),
                expected:   3
            },
            {
                message:    "Should return an array of three recipients when the parameter is an array containing three objects.",
                recipients: [
                    {email: "will@example.com", name: "Will Westin"},
                    {email: "sarah@example.com", name: "Sarah Smith"},
                    {email: "sally@example.com", name: "Sally Bronsen"}
                ],
                expected:   3
            },
            {
                message:    "Should return an array of three recipients when the parameter is an array containing three Backbone models.",
                recipients: [
                    new Backbone.Model({email: "will@example.com", name: "Will Westin"}),
                    new Backbone.Model({email: "sarah@example.com", name: "Sarah Smith"}),
                    new Backbone.Model({email: "sally@example.com", name: "Sally Bronsen"})
                ],
                expected:   3
            },
            {
                message:    "Should return an array of zero recipients when the recipient doesn't have an email address.",
                recipients: {id: "abcd", name: "Will Westin"},
                expected:   0
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field.format(data.recipients);

                expect(Array.isArray(actual)).toBe(true);
                expect(actual.length).toBe(data.expected);
            });
        }, this);
    });

    describe('UX for recipient pill formatting and wrapping pills around cc links', function() {
        it("Should set the data-content-before attribute of the select2-choices ul element for the field.", function() {
            var actual = 'Test string';

            field.render();
            field.setContentBefore(actual);

            expect(field.$('.select2-choices').attr('data-content-before')).toBe(actual);
        });
    });

    describe('format a recipient', function() {
        dataProvider = [
            {
                message:   'should return an empty object when the recipient is not a Backbone.Model',
                recipient: {module: 'Contacts', name: 'Will Westin'},
                expected:  {}
            },
            {
                message:   'should return an empty object when the recipient has no id or email',
                recipient: new Backbone.Model({module: 'Contacts', name: 'Will Westin'}),
                expected:  {}
            },
            {
                message:   'should return an object without an email when the recipient has an id and no email',
                recipient: new Backbone.Model({id: 'abcd', module: 'Contacts', name: 'Will Westin'}),
                expected:  {id: 'abcd', module: 'Contacts', locked: false, name: 'Will Westin'}
            },
            {
                message:   'should return an object with the email for an id when the recipient has an email and no id',
                recipient: new Backbone.Model({module: 'Contacts', name: 'Will Westin', email: 'will@example.com'}),
                expected:  {id: 'will@example.com', module: 'Contacts', locked: false, name: 'Will Westin', email: 'will@example.com'}
            },
            {
                message:   'should find the primary email address when the recipient has an more than one email',
                recipient: new Backbone.Model(
                    {
                        id:     'abcd',
                        module: 'Contacts',
                        name:   'Will Westin',
                        email:  [
                            {
                                email_address:   'will.westin@example.com',
                                primary_address: false
                            },
                            {
                                email_address:   'will@example.com',
                                primary_address: true
                            }
                        ]
                    }
                ),
                expected:  {id: 'abcd', module: 'Contacts', locked: false, name: 'Will Westin', email: 'will@example.com'}
            },
            {
                message:   'should not find the primary email address when the recipient has an more than one email but no primary address',
                recipient: new Backbone.Model(
                    {
                        id:     'abcd',
                        module: 'Contacts',
                        name:   'Will Westin',
                        email:  [
                            {
                                email_address:   'will.westin@example.com',
                                primary_address: false
                            },
                            {
                                email_address:   'will@example.com',
                                primary_address: false
                            }
                        ]
                    }
                ),
                expected:  {id: 'abcd', module: 'Contacts', locked: false, name: 'Will Westin'}
            },
            {
                message:   'should return an object with all properties when the recipient has an id, module, name, and email',
                recipient: new Backbone.Model(
                    {
                        id:     'abcd',
                        module: 'Contacts',
                        name:   'Will Westin',
                        email:  'will@example.com'
                    }
                ),
                expected:  {id: 'abcd', module: 'Contacts', locked: false, name: 'Will Westin', email: 'will@example.com'}
            },
            {
                message:   'should return an object with all properties when the recipient has an id, module, full_name, and email',
                recipient: new Backbone.Model(
                    {
                        id:        'abcd',
                        module:    'Contacts',
                        full_name: 'Will Westin',
                        email:     'will@example.com'
                    }
                ),
                expected:  {id: 'abcd', module: 'Contacts', locked: false, name: 'Will Westin', email: 'will@example.com'}
            },
            {
                message:   'should prioritize the recipient attributes when the recipient has a bean',
                recipient: new Backbone.Model(
                    {
                        id:     'abcd',
                        module: 'Contacts',
                        name:   'Will Westin',
                        email:  'will@example.com',
                        bean:   new Backbone.Model(
                            {
                                id:     'efgh',
                                module: 'Leads',
                                name:   'Sarah Smith',
                                email:  'sarah@example.com'
                            }
                        )
                    }
                ),
                expected:  {id: 'abcd', module: 'Contacts', locked: false, name: 'Will Westin', email: 'will@example.com'}
            },
            {
                message:   'should fall back to the bean attributes when the recipient is lacking any data',
                recipient: new Backbone.Model(
                    {
                        name:   'Will Westin',
                        email:  'will@example.com',
                        bean:   new Backbone.Model(
                            {
                                id:     'efgh',
                                module: 'Leads',
                                name:   'Sarah Smith',
                                email:  'sarah@example.com'
                            }
                        )
                    }
                ),
                expected:  {id: 'efgh', module: 'Leads', locked: false, name: 'Will Westin', email: 'will@example.com'}
            }
        ];
        _.each(dataProvider, function(data) {
            it(data.message, function() {
                var actual = field._formatRecipient(data.recipient);
                expect(actual).toEqual(data.expected);
            });
        }, this);
    });

    describe('validate an email address', function() {
        var apiCallStub;

        afterEach(function() {
            apiCallStub.restore();
        });

        it("Should return false when the api call results in an error.", function() {
            apiCallStub = sinon.stub(app.api, "call", function(method, url, data, callbacks) {
                callbacks.error();
            })

            var actual = field._validateEmailAddress("foo");
            expect(actual).toBeFalsy();
        });

        it("Should return false when the api call is successful and returns false.", function() {
            var emailAddress = "foo@bar.",
                actual;

            apiCallStub = sinon.stub(app.api, "call", function(method, url, data, callbacks) {
                var result = {};
                result[emailAddress] = false;
                callbacks.success(result);
            });

            actual = field._validateEmailAddress(emailAddress);
            expect(actual).toBeFalsy();
        });

        it("Should return true when the api call is successful and returns true.", function() {
            var emailAddress = "foo@bar.com",
                actual;

            apiCallStub = sinon.stub(app.api, "call", function(method, url, data, callbacks) {
                var result = {};
                result[emailAddress] = true;
                callbacks.success(result);
            });

            actual = field._validateEmailAddress(emailAddress);
            expect(actual).toBeTruthy();
        });
    });
});
