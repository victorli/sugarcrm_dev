describe("Profile Actions", function() {

    var app, view, sinonSandbox, menuMeta;
    beforeEach(function() {
        var context;
        app = SugarTest.app;
        SugarTest.testMetadata.init();
        SugarTest.loadHandlebarsTemplate('profileactions', 'view', 'base');
        SugarTest.loadPlugin('Dropdown');
        SugarTest.testMetadata.set();
        context = app.context.getContext();
        view = SugarTest.createView("base","Accounts", "profileactions", null, context);
        sinonSandbox = sinon.sandbox.create();
        menuMeta = [{
            acl_action: 'admin',
            label: 'LBL_ADMIN'
        }];
    });
    afterEach(function() {
        SugarTest.testMetadata.dispose();
        app.cache.cutAll();
        app.view.reset();
        sinonSandbox.restore();
        Handlebars.templates = {};
        view.dispose();
        view = null;
        menuMeta = null;
        delete app.plugins.plugins['layout']['Dropdown'];
        delete app.plugins.plugins['view']['Dropdown'];
    });

    it("should show admin link when acl of admin and developer", function() {
        var stubAdminAndDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(a) {
            if (a === 'admin' || a === 'developer') {
                return true;
            } else {
                return false;
            }
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubAdminAndDev).toHaveBeenCalled();
        expect(result.length).toEqual(1);
    });
    it("should show admin link when acl of developer", function() {
        var stubDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(a) {
            if (a === 'developer') {
                return true;
            } else {
                return false;
            }
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(stubDev).toHaveBeenCalled();
        expect(result.length).toEqual(1);
    });
    it("should NOT show admin link when acl is NOT of admin or developer", function() {
        var notAdminOrDev = sinonSandbox.stub(app.acl, 'hasAccessToAny', function(a) {
            return false;
        });
        var result = view.filterAvailableMenu(menuMeta);
        expect(notAdminOrDev).toHaveBeenCalled();
        expect(result.length).toEqual(0);
    });

    describe('keyboard events for dropdown submenus', function() {
        var event,
            spyOnClick;

        beforeEach(function() {
            view.meta = [
                {
                    route: '#bwc/index.php?module=Users&action=DetailView&record=',
                    label: 'LBL_PROFILE',
                    acl_action: 'view',
                    submenu: [
                        {
                            label: 'LNK_ABOUT',
                            acl_action: 'view',
                            route: '#about'
                        },
                        {
                            label: 'LNK_LOGOUT',
                            route: '#logout/?clear=1'
                        }
                    ]
                },
                {
                    route: '#about',
                    label: 'LNK_ABOUT',
                    acl_action: 'view',
                    submenu: []
                },
                {
                    route: '#logout/?clear=1',
                    label: 'LBL_LOGOUT',
                    submenu: []
                }
            ];
            sinonSandbox.stub(app.api, 'isAuthenticated').returns(true);
            sinonSandbox.stub(view, 'filterAvailableMenu').returns(view.meta);
            view.render();
            // need to append the layout to the DOM so bootstrap-dropdown can
            // hear the events
            $('body').append(view.$el);
            event = $.Event('keydown');
            spyOnClick = sinonSandbox.spy();
        });

        afterEach(function() {
            // must remove the view from the DOM
            view.dispose();
            event = undefined;
        });

        describe('when an item has a submenu', function() {
            beforeEach(function() {
                var toggle = view.$('[data-toggle="dropdown"]').first(),
                    dropdown = toggle.parent(),
                    profileItem = dropdown.find('.dropdown-menu a').first();
                // toggle open the first dropdown
                toggle.click();
                // apply focus to the profile item
                profileItem.focus();
                profileItem.find('.dropdown-submenu').on('click', spyOnClick);
            });

            it('should expand/collapse submenu with `left` and `right` keys', function() {
                // open the submenu
                event.keyCode = event.which = $.ui.keyCode.RIGHT;
                view.$el.trigger(event);
                // now close the submenu... requires a brand new event
                event = $.Event('keydown');
                event.keyCode = event.which = $.ui.keyCode.LEFT;
                view.$el.trigger(event);
                expect(spyOnClick.callCount).toBe(2);
            });
        });

        describe('when an item does not have a submenu', function() {
            it('should ignore `left` and `right` keys', function() {
                // same code
                var toggle = view.$('[data-toggle="dropdown"]').eq(1),
                    dropdown = toggle.parent(),
                    aboutItem = dropdown.find('.dropdown-menu a').first(),
                    left = $.Event('keydown', {keyCode: $.ui.keyCode.LEFT}),
                    right = $.Event('keydown', {keyCode: $.ui.keyCode.RIGHT});

                // toggle open the about dropdown
                toggle.click();
                // apply focus to the about item
                aboutItem.focus();
                // now attempt to open the submenu
                view.$el.trigger(left).trigger(right);
                expect(spyOnClick).not.toHaveBeenCalled();
            });
        });
    });
});
