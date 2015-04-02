(function(){

var underscoreDelayFunctions = ['throttle', 'debounce'];
var underscoreSetTimeoutFunctions = ['delay', 'defer'];

beforeEach(function(){
    if (!(SugarTest.clock && SugarTest.clock.restore))
    {
        SugarTest.clock = sinon.useFakeTimers();
    }

    SugarTest.components = [];
    SugarTest.datas = [];
    SugarTest._events = {
        context: [],
        model: []
    };


    //Mock throttle and debounce to prevent the need to actually wait.
    //(underscore throttle uses dates to enforce waits outside of the normal setTimeout function
    _.each(underscoreDelayFunctions, function(func) {
        if (_[func].restore) {
            return;
        }

        sinon.stub(_, func, function(f) {
            return function() {
                f.apply(this, arguments);
            };
        });
    });

    //mock delay and defer to prevent the need to actually wait.
    //we want to invoke the stubbed method right away
    _.each(underscoreSetTimeoutFunctions, function(func) {
        if (_[func].restore) {
            return;
        }

        sinon.stub(_, func, function(f) {
            var args = Array.prototype.slice.call(arguments, 1);
            f.apply(null, args);
        });
    });

    // stub out the icon helper so that tests calling templates don't bomb...
    Handlebars.registerHelper('moduleIconLabel', function(module) {
        return module.substring(0, 2);
    });

});

afterEach(function() {
    _.each(SugarTest.datas, function(module) {
        SugarTest.app.data.resetModel(module);
        SugarTest.app.data.resetCollection(module);
    });
    _.each(SugarTest.components, function(component) {
        component.dispose();
    });
    var suite = this.suite;
    while(suite.parentSuite) {
        suite = suite.parentSuite;
    }
    var suiteDesc = suite.description,
        url = window.location.origin + window.location.pathname + "?spec=" + escape(suiteDesc),
        msgCss = "color:white;background-color:red;";

    _.each(SugarTest._events, function(evts, type) {
        _.each(evts, function(stack, idx) {
            _.each(stack, function(ctx, name) {
                if(!_.isEmpty(ctx)) {
                    if(type == "model") {
                        _.each(ctx, function(cb){
                            if(!(cb.context instanceof Backbone.Model || cb.context instanceof Backbone.Collection)) {
                                if(idx === 0) {
                                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".on("  + name + ") - '" + url + "'", msgCss);
                                } else if(idx === 0) {
                                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".before("  + name + ") - '" + url + "'", msgCss);
                                }
                            }
                        });
                    } else {
                        if(idx === 0) {
                            console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".on("  + name + ") - '" + url + "'", msgCss);
                        } else if(idx === 0) {
                            console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".before("  + name + ") - '" + url + "'", msgCss);
                        }
                    }
                }
                delete stack[name];
            }, this);
        }, this);
    }, this);

    var type = 'app.routing';
    _.each([SugarTest.app.routing._events, SugarTest.app.routing._before], function(stack, idx) {
        _.each(stack, function(ctx, name) {
            if(!_.isEmpty(ctx)) {
                if(idx === 0) {
                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".on("  + name + ") - '" + url + "'", msgCss);
                    delete SugarTest.app.router._events[name];
                } else if(idx === 0) {
                    console.log("%c[DISPOSE NEEDED]" + suiteDesc + ":" + type + ".before("  + name + ") - '" + url + "'", msgCss);
                    delete SugarTest.app.router._before[name];
                }
            }
        }, this);
    }, this);
    SugarTest.components = null;
    SugarTest._events = null;
    
    delete Handlebars.helpers.moduleIconLabel;

    SugarTest.clock.restore();
    _.each(underscoreDelayFunctions, function(func) {
        if (_[func].restore) {
            _[func].restore();
        }
    });
    _.each(underscoreSetTimeoutFunctions, function(func) {
        if (_[func].restore) {
            _[func].restore();
        }
    });
});

})();
