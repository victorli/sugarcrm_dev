describe("Alert View", function() {
    var moduleName = 'Cases',
        app,
        sinonSandbox, view;

    beforeEach(function() {
        SugarTest.testMetadata.init();
        SugarTest.loadComponent('base', 'view', 'alert');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'process');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'confirmation');
        SugarTest.loadHandlebarsTemplate('alert', 'view', 'base', 'error');
        SugarTest.testMetadata.set();

        app = SugarTest.app;
        sinonSandbox = sinon.sandbox.create();

        view = SugarTest.createView('base', moduleName, 'alert');
    });

    afterEach(function() {
        SugarTest.testMetadata.dispose();
        SugarTest.app.view.reset();
        sinonSandbox.restore();
    });

    describe('getTranslatedLabels()', function() {
        it("Should return a translated string when a string is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    FOO: 'bar'
                }
            });

            expect(view.getTranslatedLabels('FOO').string).toBe('bar');
        });

        it("Should return a translated array of strings when an array is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    FOO: 'bar'
                }
            });
            var result = view.getTranslatedLabels(['FOO','FOO','FOO']);

            expect(_.isArray(result)).toBe(true);
            _.each(result , function(text) {
                expect(text.string).toBe('bar');
            });
        });
    });

    describe('getAlertTemplate()', function() {
        it("Should return the correct class when success level is given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    FOO: 'foo',
                    BAR: 'bar'
                }
            });

            var dataProvider = {};
            dataProvider[view.LEVEL.SUCCESS] = 'alert-success';
            dataProvider[view.LEVEL.PROCESS] = 'alert-process';
            dataProvider[view.LEVEL.WARNING] = 'alert-warning';
            dataProvider[view.LEVEL.INFO] = 'alert-info';
            dataProvider[view.LEVEL.ERROR] = 'alert-danger';
            dataProvider[view.LEVEL.CONFIRMATION] = 'alert-warning';

            _.each(dataProvider, function(className, level) {
                var result = view.getAlertTemplate(level, 'BAR', 'FOO');
                expect($('<div></div>').append(result).find('.alert').hasClass(className)).toBe(true);
            });
        });

        it("Should return the default title if title is not given", function() {
            sinonSandbox.stub(app.metadata, 'getStrings', function() {
                return {
                    LBL_ALERT_TITLE_SUCCESS: 'foo bar'
                }
            });

            var result = view.getAlertTemplate(view.LEVEL.SUCCESS, 'BAR');
            expect(result.indexOf('foo bar')).not.toBe(-1);
        });

        it('should clear double ellipsis on processing labels', function() {
            var result;
            result = view.getAlertTemplate(view.LEVEL.PROCESS, null, 'Loading...');
            expect($(result).text()).toBe('Loading...');
            result = view.getAlertTemplate(view.LEVEL.PROCESS, null, 'Deleting...');
            expect($(result).text()).toBe('Deleting...');
        });
    });

    describe('confirmation alerts', function() {
        it('should cancel alert before calling onCancel and onConfirm', function() {
            var calledLast,
                cancelStub;
            view.onCancel = function() {
                calledLast = 'onCancel';
            };
            view.onConfirm = function() {
                calledLast = 'onConfirm';
            };
            cancelStub = sinon.collection.stub(view, 'cancel', function() {
                calledLast = 'cancel';
            });

            //Test onCancel
            view.cancelClicked();
            expect(cancelStub).toHaveBeenCalledOnce();
            expect(calledLast).toEqual('onCancel');
            //Test onConfirm
            view.confirmClicked();
            expect(cancelStub).toHaveBeenCalledTwice();
            expect(calledLast).toEqual('onConfirm');

        });

        var alertClass;

        describe("when button objects aren't use for the confirmation buttons", function() {
            beforeEach(function() {
                alertClass = app.view.views['BaseAlertView'];
            });

            it('should set onConfirm from options.onConfirm', function() {
                var alert = new alertClass({level: 'confirmation', onConfirm: 'confirm'});
                expect(alert.onConfirm).toEqual('confirm');
            });

            it('should set confirmLabel to the default label', function() {
                var alert = new alertClass({level: 'confirmation'});
                expect(alert.confirmLabel).toEqual('LBL_CONFIRM_BUTTON_LABEL');
            });

            it('should set onCancel from options.onCancel', function() {
                var alert = new alertClass({level: 'confirmation', onCancel: 'cancel'});
                expect(alert.onCancel).toEqual('cancel');
            });

            it('should set cancelLabel to the default label', function() {
                var alert = new alertClass({level: 'confirmation'});
                expect(alert.cancelLabel).toEqual('LBL_CANCEL_BUTTON_LABEL');
            });

            it('should prioritize options.onConfirm over options.confirm.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    onConfirm: 'foo',
                    confirm: {
                        callback: 'bar'
                    }
                });
                expect(alert.onConfirm).toEqual('foo');
            });

            it('should prioritize options.onCancel over options.cancel.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    onCancel: 'foo',
                    cancel: {
                        callback: 'bar'
                    }
                });
                expect(alert.onCancel).toEqual('foo');
            });
        });

        describe('when button objects are use for the confirmation buttons', function() {
            beforeEach(function() {
                alertClass = app.view.views['BaseAlertView'];
            });

            it('should set onConfirm from options.confirm.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    confirm: {
                        callback: 'confirm'
                    }
                });
                expect(alert.onConfirm).toEqual('confirm');
            });

            it('should set confirmLabel to the custom label', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    confirm: {
                        label: 'LBL_CONFIRM'
                    }
                });
                expect(alert.confirmLabel).toEqual('LBL_CONFIRM');
            });

            it('should set onCancel from options.cancel.callback', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    cancel: {
                        callback: 'cancel'
                    }
                });
                expect(alert.onCancel).toEqual('cancel');
            });

            it('should set cancelLabel to the custom label', function() {
                var alert = new alertClass({
                    level: 'confirmation',
                    cancel: {
                        label: 'LBL_CANCEL'
                    }
                });
                expect(alert.cancelLabel).toEqual('LBL_CANCEL');
            });
        });
    });

    describe('Key bindings', function() {
        var oldShortcuts;

        beforeEach(function() {
            oldShortcuts = app.shortcuts;
            app.shortcuts = {
                saveSession: sinon.stub(),
                createSession: sinon.stub(),
                register: sinon.stub(),
                restoreSession: sinon.stub()
            };
        });

        afterEach(function() {
            app.shortcuts = oldShortcuts;
        });

        it('Should create a new shortcut session and register new keys for confirmation alerts', function() {
            view.render({
                level: 'confirmation'
            });

            expect(app.shortcuts.createSession.calledOnce).toBe(true);
            expect(app.shortcuts.register.called).toBe(true);
        });

        it('Should not create a new shortcut session for other alerts', function() {
            view.render({
                level: 'warning'
            });

            expect(app.shortcuts.createSession.called).toBe(false);
        });

        it('Should restore previous shortcut session when confirmation alert is closed', function() {
            view.level = 'confirmation';
            view.render({
                level: 'confirmation'
            });
            view.close();

            expect(app.shortcuts.restoreSession.calledOnce).toBe(true);
        });

        it('Should not restore previous shortcut session when other alerts are closed', function() {
            view.level = 'warning';
            view.render({
                level: 'warning'
            });
            view.close();

            expect(app.shortcuts.restoreSession.called).toBe(false);
        });
    });
});
