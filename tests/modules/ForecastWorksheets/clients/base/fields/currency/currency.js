describe('ForecastWorksheets.Base.Fields.Currency', function() {

    var app;
    var model;

    var moduleName;
    var metadata;

    beforeEach(function() {
        SugarTest.loadPlugin('ClickToEdit');
        SugarTest.loadComponent('base', 'field', 'currency');
        SugarTest.testMetadata.init();
        SugarTest.testMetadata.set();
        moduleName = 'ForecastWorksheets';
        metadata = {
            fields: {
                "amount": {
                    "name": "amount",
                    "vname": "LBL_AMOUNT",
                    "type": "currency",
                    "dbType": "currency",
                    "comment": "Unconverted amount of the opportunity",
                    "importable": "required",
                    "duplicate_merge": "1",
                    "required": true,
                    "options": "numeric_range_search_dom",
                    "enable_range_search": true,
                    "validation": {
                        "type": "range",
                        "min": 0
                    }
                },
                "currency_id": {
                    "name": "currency_id",
                    "type": "id",
                    "group": "currency_id",
                    "vname": "LBL_CURRENCY",
                    "function": {
                        "name": "getCurrencyDropDown",
                        "returns": "html"
                    },
                    "reportable": false,
                    "comment": "Currency used for display purposes"
                },
                "base_rate": {
                    "name": "base_rate",
                    "vname": "LBL_CURRENCY_RATE",
                    "type": "double",
                    "required": true
                }
            },
            views: [],
            layouts: [],
            _hash: "d7e699e7cf748d05ac311b0165e7591a"
        };

        app = SugarTest.app;

        app.data.declareModel(moduleName, metadata);
        app.user.setPreference('currency_id', '-99');

        model = app.data.createBean(moduleName, {
            amount: 123456789.12,
            currency_id: '-99',
            base_rate: 1
        });
        model.isCopy = function() {
            return (model.isCopied === true);
        };
    });

    afterEach(function() {
        delete app.plugins.plugins['field']['ClickToEdit'];
        delete app.plugins.plugins['view']['CteTabbing'];
        app.cache.cutAll();
        app.view.reset();
        Handlebars.templates = {};
        model = null;

        moduleName = null;
        metadata = null;
        SugarTest.testMetadata.dispose();
    });

    describe('ClickToEdit Plugin', function() {
        var field;
        beforeEach(function() {
            field = SugarTest.createField(
                'base',
                'amount',
                'currency',
                'detail',
                {
                    related_fields: ['currency_id', 'base_rate'],
                    currency_field: 'currency_id',
                    base_rate_field: 'base_rate'
                },
                moduleName,
                model,
                undefined,
                true
            );
            field.action = 'detail'
            sinon.stub(field, 'render', function() {
            });
            sinon.stub(field, 'setCurrencyValue', function() {
            });
        });

        afterEach(function() {
            field.render.restore();
            field.setCurrencyValue.restore();
            field = null;
        });


        it('should have ClickToEdit Plugin registered', function() {
            expect(field.plugins).toContain('ClickToEdit');
        });


        describe('ClickToEdit fieldValueChanged', function() {
            var sandbox = sinon.sandbox.create();
            beforeEach(function() {
                field.value = '1.000000';
            });
            afterEach(function() {
                field.value = undefined;
                sandbox.restore();
            });

            it('should return true when adding 1', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '+1.000000';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when subtracting 1', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '-1.000000';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when adding a percent', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '+1%';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return true when subtracting a percent', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '-1%';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeTruthy();
            });

            it('should return false when values are the same', function() {
                sandbox.stub(field.$el, 'find', function() {
                    return {
                        val: function() {
                            return '1.000000';
                        }
                    }
                });
                expect(field.fieldValueChanged(field)).toBeFalsy();
            });
        });

        describe('_verifyCurrencyValue', function() {
            beforeEach(function() {
                field.value = '1.000000';
                app.user.setPreference('number_grouping_separator', 'A');
                app.user.setPreference('decimal_separator', 'z');
            });
            afterEach(function() {
                field.value = undefined;
                app.user.setPreference('number_grouping_separator', ',');
                app.user.setPreference('decimal_separator', '.');
            });

            describe('when using users preferences', function() {
                it('should return true when format matches users format', function() {
                    expect(field._verifyCurrencyValue('1A000z00')).toBeTruthy();
                });

                it('should return true when number starts with decimal separator', function() {
                    expect(field._verifyCurrencyValue('z05')).toBeTruthy();
                });

                it('should return false when format does not match users format', function() {
                    expect(field._verifyCurrencyValue('1,000x00')).toBeFalsy();
                });

                it('should return false when number starts with an invalid decimal separator', function() {
                    expect(field._verifyCurrencyValue('.05')).toBeFalsy();
                });
            });

            describe('should fall back to system defaults when users are undefined', function() {
                var sandbox = sinon.sandbox.create();
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sandbox.stub(app.metadata, 'getConfig', function() {
                        return {
                            'defaultDecimalSeparator': 'd',
                            'defaultNumberGroupingSeparator': 'g'
                        }
                    });
                });

                afterEach(function() {
                    sandbox.restore();
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('and return false when they do not match', function() {
                    expect(field._verifyCurrencyValue('1,0000x00')).toBeFalsy();
                });

                it('and return true when they do match the system defaults', function() {
                    expect(field._verifyCurrencyValue('d05')).toBeTruthy();
                    expect(field._verifyCurrencyValue('122g212d05')).toBeTruthy();
                });
            });

            describe('should fall back to hardcoded values when no user prefs and no system defaults', function() {
                var sandbox = sinon.sandbox.create();
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sandbox.stub(app.metadata, 'getConfig', function() {
                        return {}
                    });
                });

                afterEach(function() {
                    sandbox.restore();
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('and return false when they do not match', function() {
                    expect(field._verifyCurrencyValue('1,0000x00')).toBeFalsy();
                });

                it('and return true when they do match the system defaults', function() {
                    expect(field._verifyCurrencyValue('.05')).toBeTruthy();
                    expect(field._verifyCurrencyValue('122,212.05')).toBeTruthy();
                });
            });
        });

        describe('_parsePercentage', function() {
            var sandbox = sinon.sandbox.create();
            beforeEach(function() {
                sandbox.stub(field.model, 'get', function() {
                    return '1.000000'
                });
                field.value = '1.000000';
            });
            afterEach(function() {
                sandbox.restore();
                field.value = undefined;
                app.user.setPreference('number_grouping_separator', ',');
                app.user.setPreference('decimal_separator', '.');
            });

            describe('should use users preferences', function() {
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', 'A');
                    app.user.setPreference('decimal_separator', ',');
                });

                afterEach(function() {
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.');
                });

                it('should increase field value by +50.8%', function() {
                    expect(field._parsePercentage('+50,8%')).toEqual('$1,51');
                });

                it('should increase field value by +50.8', function() {
                    expect(field._parsePercentage('+50,8')).toEqual('$51,80');
                });

                it('should decrease field value by -50.8%', function() {
                    expect(field._parsePercentage('-50,8%')).toEqual('$0,49');
                });

                it('should decrease field value by -50.8', function() {
                    expect(field._parsePercentage('-50,8')).toEqual('$-49,80');
                });

                it('should increase field value by +.5', function() {
                    expect(field._parsePercentage('+,5')).toEqual('$1,50');
                });

                it('should decrease field value by -.5', function() {
                    expect(field._parsePercentage('-,5')).toEqual('$0,50');
                });

                it('should increase field value by +.5%', function() {
                    expect(field._parsePercentage('+,5%')).toEqual('$1,00');
                });

                it('should decrease field value by -.5%', function() {
                    expect(field._parsePercentage('-,5%')).toEqual('$0,99');
                });

                it('should increase field value by +1000', function() {
                    expect(field._parsePercentage('+1000')).toEqual('$1A001,00');
                });

                it('should decrease field value by -1000', function() {
                    expect(field._parsePercentage('-1000')).toEqual('$-999,00');
                });

                it('should increase field value by +1A000', function() {
                    expect(field._parsePercentage('+1A000')).toEqual('$1A001,00');
                });

                it('should decrease field value by -1A000', function() {
                    expect(field._parsePercentage('-1A000')).toEqual('$-999,00');
                });
            });

            describe('should fall back to system defaults when users are undefined', function() {
                var sandbox = sinon.sandbox.create();
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sandbox.stub(app.metadata, 'getConfig', function() {
                        return {
                            'defaultDecimalSeparator': 'd',
                            'defaultNumberGroupingSeparator': 'g'
                        }
                    });
                    sandbox.stub(field, 'format', function(amount, currencyId) {
                        return app.currency.formatAmount(amount, currencyId, undefined, 'g', 'd');
                    });

                    sandbox.stub(field, 'unformat', function(amount) {
                        return app.utils.unformatNumberString(amount, 'g', 'd');
                    });
                });

                afterEach(function() {
                    sandbox.restore();
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('should increase field value by +50.8%', function() {
                    expect(field._parsePercentage('+50d8%')).toEqual('$1d51');
                });

                it('should increase field value by +50.8', function() {
                    expect(field._parsePercentage('+50d8')).toEqual('$51d80');
                });

                it('should decrease field value by -50.8%', function() {
                    expect(field._parsePercentage('-50d8%')).toEqual('$0d49');
                });

                it('should decrease field value by -50.8', function() {
                    expect(field._parsePercentage('-50d8')).toEqual('$-49d80');
                });

                it('should increase field value by +.5', function() {
                    expect(field._parsePercentage('+d5')).toEqual('$1d50');
                });

                it('should decrease field value by -.5', function() {
                    expect(field._parsePercentage('-d5')).toEqual('$0d50');
                });

                it('should increase field value by +.5%', function() {
                    expect(field._parsePercentage('+d5%')).toEqual('$1d00');
                });

                it('should decrease field value by -.5%', function() {
                    expect(field._parsePercentage('-d5%')).toEqual('$0d99');
                });

                it('should increase field value by +1000', function() {
                    expect(field._parsePercentage('+1000')).toEqual('$1g001d00');
                });

                it('should decrease field value by -1000', function() {
                    expect(field._parsePercentage('-1000')).toEqual('$-999d00');
                });

                it('should increase field value by +1,000', function() {
                    expect(field._parsePercentage('+1g000')).toEqual('$1g001d00');
                });

                it('should decrease field value by -1,000', function() {
                    expect(field._parsePercentage('-1g000')).toEqual('$-999d00');
                });
            });

            describe('should fall back to hardcoded values when no user prefs and no system defaults', function() {
                var sandbox = sinon.sandbox.create();
                beforeEach(function() {
                    app.user.setPreference('number_grouping_separator', undefined);
                    app.user.setPreference('decimal_separator', undefined);
                    sandbox.stub(app.metadata, 'getConfig', function() {
                        return {}
                    });
                });

                afterEach(function() {
                    sandbox.restore();
                    app.user.setPreference('number_grouping_separator', ',');
                    app.user.setPreference('decimal_separator', '.')
                });

                it('should increase field value by +50.8%', function() {
                    expect(field._parsePercentage('+50.8%')).toEqual('$1.51');
                });

                it('should decrease field value by -50.8%', function() {
                    expect(field._parsePercentage('-50.8%')).toEqual('$0.49');
                });

                it('should increase field value by +.5%', function() {
                    expect(field._parsePercentage('+.5%')).toEqual('$1.00');
                });

                it('should decrease field value by -.5%', function() {
                    expect(field._parsePercentage('-.5%')).toEqual('$0.99');
                });

                it('should increase field value by +50.8', function() {
                    expect(field._parsePercentage('+50.8')).toEqual('$51.80');
                });

                it('should decrease field value by -50.8', function() {
                    expect(field._parsePercentage('-50.8')).toEqual('$-49.80');
                });

                it('should increase field value by +.5', function() {
                    expect(field._parsePercentage('+.5')).toEqual('$1.50');
                });

                it('should decrease field value by -.5', function() {
                    expect(field._parsePercentage('-.5')).toEqual('$0.50');
                });

                it('should increase field value by +1000', function() {
                    expect(field._parsePercentage('+1000')).toEqual('$1,001.00');
                });

                it('should decrease field value by -1000', function() {
                    expect(field._parsePercentage('-1000')).toEqual('$-999.00');
                });

                it('should increase field value by +1,000', function() {
                    expect(field._parsePercentage('+1,000')).toEqual('$1,001.00');
                });

                it('should decrease field value by -1,000', function() {
                    expect(field._parsePercentage('-1,000')).toEqual('$-999.00');
                });
            });
        });

        describe('validateField', function() {
            var sandbox = sinon.sandbox.create();
            beforeEach(function() {
                sandbox.stub(field.model, 'get', function() {
                    return '1.000000'
                });
                field.value = '1.000000';
                app.user.setPreference('number_grouping_separator', undefined);
                app.user.setPreference('decimal_separator', undefined);
                sandbox.stub(app.metadata, 'getConfig', function() {
                    return {}
                });
            });

            afterEach(function() {
                sandbox.restore();
                app.user.setPreference('number_grouping_separator', ',');
                app.user.setPreference('decimal_separator', '.')
            });

            it('should return false for +0,5', function() {
                expect(field.validateField(field, '+0,5')).toBeFalsy();
            });

            it('should return false for +,5', function() {
                expect(field.validateField(field, '+,5')).toBeFalsy();
            });

            it('should return false for -0,5', function() {
                expect(field.validateField(field, '-0,5')).toBeFalsy();
            });

            it('should return false for -,5', function() {
                expect(field.validateField(field, '-,5')).toBeFalsy();
            });

            it('should return 1.50 for +0.5', function() {
                expect(field.validateField(field, '+0.5')).toEqual("$1.50");
            });

            it('should return 0.50 for -0.5', function() {
                expect(field.validateField(field, '-0.5')).toEqual("$0.50");
            });

            it('should return 1.50 for +.5', function() {
                expect(field.validateField(field, '+.5')).toEqual("$1.50");
            });

            it('should return 0.50 for -.5', function() {
                expect(field.validateField(field, '-.5')).toEqual("$0.50");
            });

            it('should return false for 1000.00+100', function() {
                expect(field.validateField(field, '1000.00+100')).toBeFalsy();
            });

            it('should return false for 1000.00-100', function() {
                expect(field.validateField(field, '1000.00-100')).toBeFalsy();
            });

            it('should return false for 1000.00+asdfasdfa', function() {
                expect(field.validateField(field, '1000.00+asdfasdfa')).toBeFalsy();
            });

            it('should return false for 1000.00asdfasdfa', function() {
                expect(field.validateField(field, '1000.00asdfasdfa')).toBeFalsy();
            });

            it('should increase field value by +1000', function() {
                expect(field.validateField(field, '+1000')).toEqual('$1,001.00');
            });

            it('should return false for -1000 as negative numbers are not supported', function() {
                expect(field.validateField(field, '-1000')).toBeFalsy();
            });

            it('should return false for +1,000,00.00', function() {
                expect(field.validateField(field, '+1,000,00.00')).toBeFalsy();
            });
        });
    });
});
