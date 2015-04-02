describe('Sugar7 Language', function() {
    var app;
    beforeEach(function() {
        app = SugarTest.app;
    });
    afterEach(function() {
        sinon.collection.restore();
    });
    describe('direction change', function() {
        it('should toggle rtl class based on language direction', function() {
            sinon.collection.stub(app.lang, 'setLanguage', function(lang) {
                app.lang.direction = lang === 'he_IL' ? 'rtl' : 'ltr';
                app.events.trigger('lang:direction:change');
            });
            app.lang.setLanguage('en_us');
            expect($('html').hasClass('rtl')).toBeFalsy();

            //Only enable the rtl class when the direction is `rtl`
            app.lang.setLanguage('he_IL');
            expect($('html').hasClass('rtl')).toBeTruthy();

            $('html').removeClass('rtl');
        });
    });
});
