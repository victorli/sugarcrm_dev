if (!(fixtures)) {
    var fixtures = {};
}

fixtures.search = {
    model1: {
        id: 1,
        name: 'aaa',
        _module: 'module1',
        _highlights: {
            alphaField: ['highlight1'],
            bravoField: ['highlight2']
        }
    },
    model1_fields: {
        name: {name: 'aaa'},
        alphaField: {name: 'highlight1'},
        bravoField: {name: 'highlight2'}
    },
    getModule1_return: {
        nameFormat: [],
        fields: {
            name: {vname: 'Module 1'},
            alphaField: {vname: 'Alpha'},
            bravoField: {vname: 'Bravo'}
        }
    },
    getView1_return: {
        panels: [
            {
                name: 'primary',
                fields: [
                    {name: 'name'}
                ]
            },
            {
                name: 'secondary',
                fields: [
                    {name: 'alphaField'},
                    {name: 'bravoField'}
                ]
            }
        ],
        rowactions: {
            'actions': true
        }
    },
    model2: {
        id: 2,
        name: 'bbb',
        _module: 'module2',
        _highlights: {
            first_name: ['highlight3']
        }
    },
    model2_fields: {
        first_name: {name: 'highlight3'}
    },
    getModule2_return: {
        nameFormat: {'f': 'first_name', 'l': 'last_name'},
        fields: {
            name: {vname: 'name'},
            first_name: {vname: 'first'},
            last_name: {vname: 'last'}
        }
    },
    getView2_return: {
        panels: [
            {
                name: 'primary',
                fields: [
                    {name: 'name'},
                    {name: 'first_name'},
                    {name: 'last_name'}
                ]
            },
            {
                name: 'secondary',
                fields: []
            }
        ],
        rowactions: {
            'actions': true
        }
    }
}