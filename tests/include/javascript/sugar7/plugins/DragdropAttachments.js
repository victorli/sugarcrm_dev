describe("DragdropAttachments Plugin", function() {
    var plugin;

    beforeEach(function() {
        SugarTest.loadPlugin('DragdropAttachments');
        plugin = SugarTest.app.plugins._get('DragdropAttachments', 'view');
    });

    describe("_mapNoteParentAttributes", function() {
        var dataProvider = [
            {
                message: "Should add parent if parentId and parentType specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        id: '123',
                        module: 'Accounts'
                    }
                },
                expectedAttributes: {
                    parent_id: '123',
                    parent_type: 'Accounts'
                }
            },
            {
                message: "Should not add parent if parentId not specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        module: 'Accounts'
                    }
                },
                expectedAttributes: {}
            },
            {
                message: "Should not add parent if parentType not specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        id: '123'
                    }
                },
                expectedAttributes: {}
            },
            {
                message: "Should not add parent if neither parentId or parentType specified",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                    }
                },
                expectedAttributes: {}
            },
            {
                message: "Should add parent if parentType is specified as Home",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        module: 'Home',
                        id: '123'
                    }
                },
                expectedAttributes: {
                    parent_type: 'Home'
                }
            },
            {
                message: "Should add parent if parentType is specified as Activities",
                note: new Backbone.Model(),
                attributes: {
                    model: {
                        module: 'Activities'
                    }
                },
                expectedAttributes: {
                    parent_type: 'Activities'
                }
            }
        ];

        _.each(dataProvider, function(data) {
            it(data.message, function() {
                plugin.context = {};
                plugin.context.parent = SugarTest.app.context.getContext();
                plugin.context.parent.set(data.attributes);
                var actual = plugin._mapNoteParentAttributes();
                expect(actual).toEqual(data.expectedAttributes);
            });
        }, this);
    });
});
