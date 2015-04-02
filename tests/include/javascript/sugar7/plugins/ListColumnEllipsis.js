describe("List Column Ellipsis Plugin", function() {
    var app, plugin;

    beforeEach(function() {
        app = SugarTest.app;
        // Load plugin directly so completely orthogonal to SUGAR.App
        SugarTest.loadPlugin('ListColumnEllipsis');
        plugin = app.plugins._get('ListColumnEllipsis', 'view');
        plugin._fields = {};
        plugin._fields._byId = {
            'email': {name: 'email', selected: true}
        };
        plugin._fields.visible = [
            {name: 'email', selected: true}
        ];
        plugin._fields.all = [
            {name: 'email', selected: true}
        ];
        plugin.trigger = sinon.stub();
    });
    afterEach(function() {
        // delete our fake trigger as to not affect other tests
        delete plugin.trigger;
        app = null;
    });

    it('Should determine if field being toggled is last visible column', function() {
        var actual = plugin.isLastColumnVisible('email');
        expect(actual).toEqual(true);
    });
    it('Should not toggle field if more than one field is visible', function() {
        plugin._fields.visible = [
            {name: 'email', selected: true},
            {name: 'foo', selected: true}
        ];//add one extra
        var actual = plugin.isLastColumnVisible('email');
        expect(actual).toEqual(false);
    });
    it('Should set fields toggling selected from true to false', function() {
        var opts = [
            {name: 'no1', selected: false},
            {name: 'no2', selected: false},
            {name: 'yes1', selected: true},
            {name: 'no3', selected: false}
        ];
        plugin._fields.visible = _.where(opts, { selected: true });
        _.each(opts, function(field) {
            plugin._fields._byId[field.name] = field;
        });
        plugin._fields.all = opts;
        plugin._toggleColumn('yes1');
        expect(opts[2].selected).toEqual(false);
        expect(plugin._fields.visible.length).toBeFalsy();
    });
    it('Should set fields toggling selected from false to true', function() {
        var opts = [
            {name: 'no1', selected: false},
            {name: 'yes1', selected: true},
            {name: 'no1', selected: false}
        ];
        plugin._fields.visible = _.where(opts, { selected: true });
        _.each(opts, function(field) {
            plugin._fields._byId[field.name] = field;
        });
        plugin._fields.all = opts;
        plugin._toggleColumn('yes');
        expect(opts[1].selected).toEqual(true);
        expect(plugin._fields.visible.length).toEqual(1);
    });
});
