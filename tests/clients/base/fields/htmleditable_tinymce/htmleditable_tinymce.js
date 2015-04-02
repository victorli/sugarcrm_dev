describe("htmleditable_tinymce", function() {

    describe("edit view", function() {
        var field, stub;

        beforeEach(function() {
            var $textarea = $('<textarea class="htmleditable"></textarea>');
            field = SugarTest.createField("base","html_email", "htmleditable_tinymce", "edit");
            stub = sinon.stub(field, "_getHtmlEditableField", function(){
                return $textarea;
            });
        });

        afterEach(function() {
            stub.restore();
            field = undefined;
        });

        it("should render edit view not readonly view", function() {
            var edit = sinon.spy(field, '_renderEdit');
            var view = sinon.spy(field, '_renderView');

            field.render();

            expect(edit.calledOnce).toBeTruthy();
            expect(view.called).toBeFalsy();

            edit.restore();
            view.restore();
        });

        it("should give access to TinyMCE config", function() {
            field.render();

            expect(field.getTinyMCEConfig()).toBeDefined();
        });

        it("should initialize TinyMCE editor when it doesn't exist", function() {
            var tinymceSpy = sinon.spy($.fn, 'tinymce');
            var configSpy = sinon.spy(field, 'getTinyMCEConfig');

            field.initTinyMCEEditor();

            expect(tinymceSpy.calledOnce).toBeTruthy();
            expect(configSpy.calledOnce).toBeTruthy();

            tinymceSpy.restore();
            configSpy.restore();
        });

        it("should initialize TinyMCE editor with custom config options", function() {
            var tinymceSpy = sinon.spy($.fn, 'tinymce');
            var configSpy = sinon.spy(field, 'getTinyMCEConfig');

            field.initTinyMCEEditor({
                script_url : 'include/javascript/tiny_mce/tiny_mce.js',
                theme : "advanced",
                skin : "sugar7",
                plugins : "style",
                entity_encoding: "raw",
                theme_advanced_buttons1 : "code",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : true,
                schema: "html5"
            });

            expect(tinymceSpy.calledOnce).toBeTruthy();
            expect(configSpy.called).toBeTruthy();

            tinymceSpy.restore();
            configSpy.restore();
        });

        it("should not initialize TinyMCE editor if it already exists", function() {
            var tinymceSpy = sinon.spy($.fn, 'tinymce');
            var configSpy = sinon.spy(field, 'getTinyMCEConfig');
            var config = field.getTinyMCEConfig();
            var called = 0;
            config.setup = function(){
                if(called < 1){
                    field.initTinyMCEEditor(config);
                    called++;
                } else {
                    expect(tinymceSpy.calledOnce).toBeTruthy();
                    expect(configSpy.calledOnce).toBeTruthy();
                    tinymceSpy.restore();
                    configSpy.restore();
                }
            };
            field.initTinyMCEEditor(config);

        });

        it("setting a value to the model should also set the editor with that value", function() {
            var expectedValue = 'foo';
            var setEditorContentSpy;

            field.render();
            setEditorContentSpy = sinon.spy(field, 'setEditorContent');
            field.model.set(field.name, expectedValue);

            expect(setEditorContentSpy.withArgs(expectedValue).calledOnce).toBeTruthy();

            setEditorContentSpy.restore();
        });
    });

    describe("readonly view", function() {
        var field, stub;

        beforeEach(function() {
            var $textarea = $('<iframe class="htmleditable" frameborder="0"></iframe>')
            field = SugarTest.createField("base","html_email", "htmleditable_tinymce", "detail");
            stub = sinon.stub(field, "_getHtmlEditableField", function(){
                return $textarea;
            });
        });

        afterEach(function() {
            stub.restore();
            field = undefined;
        });

        it("should render read view not edit view", function() {
            var edit = sinon.spy(field, '_renderEdit');
            var view = sinon.spy(field, '_renderView');

            field.render();

            expect(edit.called).toBeFalsy();
            expect(view.calledOnce).toBeTruthy();

            edit.restore();
            view.restore();
        });

        it("should not return TinyMCE editor", function() {
            var tinymceSpy = sinon.spy(field, 'initTinyMCEEditor');

            field.render();

            expect(tinymceSpy.called).toBeFalsy();

            tinymceSpy.restore();
        });

        it("should set the value to the div if the model is changed", function() {
            var mock, expectedValue = 'foo';

            field.render();

            mock = sinon.mock(field);
            mock.expects('setViewContent').once().withArgs(expectedValue);

            field.model.set(field.name, expectedValue);

            mock.verify();
            mock.restore();
        });
    });

});
