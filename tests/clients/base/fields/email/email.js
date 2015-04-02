describe('Base.Email', function() {

    var app, field, model, mock_addr, oldjQueryFn;

    beforeEach(function() {
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit');
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'edit-email-field');
        SugarTest.loadHandlebarsTemplate('email', 'field', 'base', 'detail');
        SugarTest.testMetadata.set();

        mock_addr =  [
            {
                email_address: "test1@test.com",
                primary_address: true
            },
            {
                email_address: "test2@test.com",
                primary_address: false,
                opt_out: true
            }
        ];

        field = SugarTest.createField("base","email", "email", "edit", undefined, undefined, new Backbone.Model({
            email: app.utils.deepCopy(mock_addr)
        }));
        model = field.model;

        if ($.fn.tooltip) {
            oldjQueryFn = $.fn.tooltip;
        }
        $.fn.tooltip = function(){};
        field.addPluginTooltips = field.removePluginTooltips = function(){};

        field.render();
    });

    afterEach(function() {
        app.cache.cutAll();
        app.view.reset();
        SugarTest.testMetadata.dispose();
        Handlebars.templates = {};
        field = null;
        if (oldjQueryFn) {
            $.fn.tooltip = oldjQueryFn;
            oldjQueryFn = null;
        }
    });

    describe("initial rendering", function() {
        it("should display two email addresses and a field to add a new address", function() {
            expect(field.$('.existingAddress').length).toBe(2);
            expect(field.$('.newEmail').length).toBe(1);
        });
        it("should set first email as the primary address", function() {
            expect(field.$('[data-emailproperty=primary_address]').eq(0).hasClass('active')).toBe(true);
        });
    });

    describe("adding an email address", function() {
        it("should add email addresses on the model when there is change on the input", function() {
            var emails;

            field.$('.newEmail')
                .val("test3@test.com")
                .trigger('change');

            emails = model.get('email');
            expect(emails[2]).toBeDefined();
            expect(emails[2].email_address).toBe("test3@test.com");
        });
        it("should add email addresses when add email button is clicked", function() {
            var emails;

            field.$('.newEmail').val("test3@test.com");
            field.$('.addEmail').click();

            emails = model.get('email');
            expect(emails[2]).toBeDefined();
            expect(emails[2].email_address).toBe("test3@test.com");
        });
        it("should clear out the new email field", function(){
            var newEmailField = field.$('.newEmail')
                .val("test3@test.com")
                .change();

            expect(newEmailField.val()).toBe('');
        });
        it("should not allow duplicates", function(){
            field.$('.newEmail')
                .val('test2@test.com')
                .trigger('change');

            expect(model.get('email').length).toBe(2);
        });
        it("should make the email address primary if it is the only address", function(){
            var emails;

            model.clear();
            field.render();
            field.$('.newEmail')
                .val('foo@test.com')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].primary_address).toBe(true);
            expect(field.$('[data-emailproperty=primary_address]').hasClass('active')).toBe(true);
        });
        it("should not make the email address primary if there are existing email addresses", function(){
            var emails;

            field.$('.newEmail')
                .val('foo@test.com')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(3);
            expect(emails[2].primary_address).toBe(false);
            expect(field.$('[data-emailproperty=primary_address]').eq(2).hasClass('active')).toBe(false);
        });
        it("should default opt_out and invalid_email to false or undefined", function(){
            var emails;

            field.$('.newEmail')
                .val('foo@test.com')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(3);
            expect(emails[2].opt_out).toBeFalsy();
            expect(emails[2].invalid_email).toBeFalsy();
            expect(field.$('[data-emailproperty=opt_out]').eq(2).hasClass('active')).toBe(false);
            expect(field.$('[data-emailproperty=invalid_email]').eq(2).hasClass('active')).toBe(false);
        });
    });

    describe("updating an email address", function() {
        it("should update email addresses on the model", function() {
            var emails;

            field.$('input')
                .first()
                .val("testChanged@test.com")
                .trigger('change');

            emails = model.get('email');
            expect(emails[0].email_address).toBe("testChanged@test.com");
        });
        it("should delete empty email address field", function(){
            var emails;

            field.$('.existingAddress')
                .first()
                .val('')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].email_address).toBe('test2@test.com');
        });
        it("should make the first email address primary if primary email address is emptied", function(){
            var emails;

            field.$('.existingAddress')
                .first()
                .val('')
                .trigger('change');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].primary_address).toBe(true);
        });
    });

    describe("removing an email address", function() {
        it("should delete email addresses on the model", function() {
            var emails = model.get('email');
            expect(emails.length).toBe(2);

            field.$('.removeEmail')
                .first()
                .trigger('click');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].email_address).toBe('test2@test.com')
        });
        it("should select another primary e-mail address if the primary is deleted", function(){
            var emails = model.get('email');
            expect(emails.length).toBe(2);
            expect(emails[0].primary_address).toBe(true);
            expect(emails[1].primary_address).toBe(false);

            field.$('.removeEmail')
                .first()
                .trigger('click');

            emails = model.get('email');
            expect(emails.length).toBe(1);
            expect(emails[0].primary_address).toBe(true);
            expect(field.$('[data-emailproperty=primary_address]').hasClass('active')).toBe(true);
        });
    });

    describe("updating email properties", function() {
        it("should update opt_out when opt out button is toggled", function() {
            expect(model.get('email')[0].opt_out).toBeUndefined();

            field.$('[data-emailproperty=opt_out]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].opt_out).toBe(true);

            field.$('[data-emailproperty=opt_out]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].opt_out).toBe(false);
        });
        it("should update invalid_email when invalid button is toggled", function() {
            expect(model.get('email')[0].invalid_email).toBeUndefined();

            field.$('[data-emailproperty=invalid_email]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].invalid_email).toBe(true);

            field.$('[data-emailproperty=invalid_email]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].invalid_email).toBe(false);
        });
        it("should update primary_address only when non-primary email address is clicked", function() {
            expect(model.get('email')[0].primary_address).toBe(true);

            field.$('[data-emailproperty=primary_address]')
                .first()
                .trigger('click');

            expect(model.get('email')[0].primary_address).toBe(true);

            field.$('[data-emailproperty=primary_address]')
                .last()
                .trigger('click');

            expect(model.get('email')[0].primary_address).toBe(false);
            expect(model.get('email')[1].primary_address).toBe(true);
        });
    });

    describe("changing the model", function() {
        it("should update the view with the updated values in detail mode", function() {
            var newValue = app.utils.deepCopy(mock_addr);
            newValue[0].email_address = 'foo@test.com';
            newValue[1].opt_out = false;

            field.setMode('detail');

            expect(field.$('a').text()).toBe('test1@test.com');
            expect(field.$('span').text()).toBe('test2@test.com');

            field.model.set('email', newValue);

            expect(field.$('a').eq(0).text()).toBe('foo@test.com');
            expect(field.$('a').eq(1).text()).toBe('test2@test.com');
        });
        it("should not render the view with the updated values in edit mode", function() {
            var newValue = app.utils.deepCopy(mock_addr);
            newValue[0].email_address = 'foo@test.com';
            newValue[1].opt_out = false;

            expect(field.$('input').eq(0).val()).toBe('test1@test.com');
            expect(field.$('[data-emailproperty=opt_out]').eq(1).hasClass('active')).toBe(true);

            field.model.set('email', newValue);

            expect(field.$('input').eq(0).val()).toBe('test1@test.com');
            expect(field.$('[data-emailproperty=opt_out]').eq(1).hasClass('active')).toBe(true);
        });
    });

    describe("decorating error", function() {
        it("should decorate each invalid email fields", function(){
            var $inputs = field.$('input');
            expect(field.$('.add-on').length).toEqual(0);
            field.decorateError({email: ["test2@test.com"]});
            expect(field.$('.add-on').length).toEqual(1);
            expect(field.$('.add-on').prop('title')).toEqual('ERROR_EMAIL');
            expect($inputs.index(field.$('.add-on').prev())).toEqual(1);
        });
        it("should decorate the first field if there isn't any primary address set", function(){
            var $inputs = field.$('input');
            var emails = model.get('email');
            emails[0].primary_address = false;
            emails[1].primary_address = false;
            expect(field.$('.add-on').length).toEqual(0);
            field.decorateError({primaryEmail: true});
            expect(field.$('.add-on').length).toEqual(1);
            expect(field.$('.add-on').prop('title')).toEqual('ERROR_PRIMARY_EMAIL');
            expect($inputs.index(field.$('.add-on').prev())).toEqual(0);
        });
    });

    describe("format and unformat", function() {
        it("should create flag email strings", function() {
            var testAddresses =[
                {
                    email_address: "test1@test.com",
                    primary_address: true
                },
                {
                    email_address: "test2@test.com",
                    primary_address: true,
                    opt_out: true
                }
            ];;
            field.addFlagLabels(testAddresses);
            expect(testAddresses[0].flagLabel).toEqual("LBL_EMAIL_PRIMARY");
            expect(testAddresses[1].flagLabel).toEqual("LBL_EMAIL_PRIMARY, LBL_EMAIL_OPT_OUT");
        });

        it("should make an email address a link when metadata allows for links and the address is not opted out or invalid", function() {
            var emails = [
                    {
                        email_address: "foo@bar.com"
                    },
                    {
                        email_address: "biz@baz.net",
                        opt_out:       false,
                        invalid_email: false
                    }
                ],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeTruthy();
            expect(actual[1].hasAnchor).toBeTruthy();
        });

        it("should not make an email address a link when metadata doesn't allow for links", function() {
            var emails = [
                    {
                        email_address: "foo@bar.com"
                    },
                    {
                        email_address: "biz@baz.net",
                        opt_out:       false,
                        invalid_email: false
                    }
                ],
                actual;

            field.def.emailLink = false;
            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
            expect(actual[1].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is opted out", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       true,
                    invalid_email: false
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is invalid", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       false,
                    invalid_email: true
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should not make an email address a link when the address is opted out and invalid", function() {
            var emails = [{
                    email_address: "foo@bar.com",
                    opt_out:       true,
                    invalid_email: true
                }],
                actual;

            actual = field.format(emails);
            expect(actual[0].hasAnchor).toBeFalsy();
        });

        it("should convert a string representing an email address into an array containing one object", function() {
            var expected = {
                    email_address:   "foo@bar.com",
                    primary_address: true,
                    hasAnchor:       true,
                    flagLabel: "LBL_EMAIL_PRIMARY"
                },
                actual;

            actual = field.format(expected.email_address);
            expect(actual.length).toBe(1);
            expect(actual[0]).toEqual(expected);
        });

        it("should still work when model value is not already set on edit in list view (SP-604)", function() {
            var expected = "abc@abc.com",
                emails = "abc@abc.com",
                actual;

            field.view.action = "list";
            field.model.set({email : ""});
            actual = field.unformat(emails);
            expect(actual[0].email_address).toEqual(expected);

            field.model.set({email : undefined});
            actual = field.unformat(emails);
            expect(actual[0].email_address).toEqual(expected);

        });

        it("should empty string model value as an empty list of e-mails (MAR-667)", function() {
            var actual;

            field.model.set("");
            actual = field.format("");
            expect(actual).toEqual("");

        });

        it("should return only a single primary email address as the value in the list view", function() {
            field = SugarTest.createField("base","email", "email", "list");
            field.render();

            var new_email_address = 'test@blah.co',
                new_assigned_email = field.unformat(new_email_address),
                expected = new_email_address,
                actual;

            actual = (_.find(new_assigned_email, function(email){
                return email.primary_address;
            })).email_address;
            expect(actual).toBe(expected);
        });

    });

    describe('when required', function() {
        var sandbox = sinon.sandbox.create();
        beforeEach(function() {
            model = new Backbone.Model();
            model.fields = {
                email1: {
                    required: true
                }
            };
            model.set('email', [{
                    email_address: 'test1@test.com',
                    primary_address: true
                }]
            );
            field = SugarTest.createField('base', 'email', 'email', 'edit', undefined, undefined, model);
            model = field.model;

            field.addPluginTooltips = field.removePluginTooltips = function()
            {};

            field.render();
        });

        afterEach(function() {
            field = null;
            sandbox.restore();
        });

        it('field def will have required as true', function() {
            expect(field.def.required).toBeTruthy();
        });

        it('field will call decorateRequired when all addresses are removed', function() {
            sandbox.stub(field, 'decorateRequired', function() {
            });

            // find the first remove button
            var el = field.$('.removeEmail').first();
            // click it
            el.click();

            // this should have been called
            expect(field.decorateRequired).toHaveBeenCalled();
        });

        it('field will remove the required placeholder after add has been called', function() {
            // since it rendered with one, we need to remove it first
            field.$('.removeEmail').first().click();
            var $el = field._getNewEmailField();
            // make sure we have the LBL_REQUIRED_FIELD in the placeholder
            expect($el.prop('placeholder')).toContain('LBL_REQUIRED_FIELD');
            // set the value and add it
            $el.val('test@test.com');
            field.$('.addEmail').first().click();
            // make sure we don't have the LBL_REQUIRED_FIELD in the place holder
            expect(field._getNewEmailField().prop('placeholder')).not.toContain('LBL_REQUIRED_FIELD');

        });


    });
});
