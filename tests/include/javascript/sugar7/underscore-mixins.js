describe('Underscore Mixins', function() {

    describe('_moveItem', function() {
        var order = [];
        var originalArray = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

        beforeEach(function() {
            order = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        });

        it('should move F before A', function() {
            expect(_.moveIndex(order, 5, 0)).toEqual(['F', 'A', 'B', 'C', 'D', 'E', 'G', 'H']);
            expect(_.moveIndex(order, 0, 5)).toEqual(originalArray);
        });

        it('should move F before B', function() {
            expect(_.moveIndex(order, 5, 1)).toEqual(['A', 'F', 'B', 'C', 'D', 'E', 'G', 'H']);
            expect(_.moveIndex(order, 1, 5)).toEqual(originalArray);
        });

        it('should move F before D', function() {
            expect(_.moveIndex(order, 5, 3)).toEqual(['A', 'B', 'C', 'F', 'D', 'E', 'G', 'H']);
            expect(_.moveIndex(order, 3, 5)).toEqual(originalArray);
        });

        it('should move F before E', function() {
            expect(_.moveIndex(order, 5, 4)).toEqual(['A', 'B', 'C', 'D', 'F', 'E', 'G', 'H']);
            expect(_.moveIndex(order, 4, 5)).toEqual(originalArray);
        });

        it('should move F before F (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 5, 5)).toEqual(originalArray);
        });

        it('should move F after G (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 5, 6)).toEqual(['A', 'B', 'C', 'D', 'E', 'G', 'F', 'H']);
            expect(_.moveIndex(order, 6, 5)).toEqual(originalArray);
        });

        it('should move F to H', function() {
            expect(_.moveIndex(order, 5, 7)).toEqual(['A', 'B', 'C', 'D', 'E', 'G', 'H', 'F']);
            expect(_.moveIndex(order, 7, 5)).toEqual(originalArray);
        });

        it('should move A to A (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 0, 0)).toEqual(originalArray);
        });

        it('should move H to H (does not make sense, should keep same order)', function() {
            expect(_.moveIndex(order, 7, 7)).toEqual(originalArray);
        });
    });
});
