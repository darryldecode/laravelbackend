angular.module('cb.content').service('TaxonomyTreeViewService',['$http',function($http) {

    var TreeView = function treeView (taxonomies){
        this.taxonomies = taxonomies;
        this.taxonomiesThatHasParents = [];
        this.tree = [];
    };

    TreeView.prototype = {

        getTaxonomiesWithChildren: function () {

            var _this = this;
            var copied = angular.copy(_this.taxonomies);

            angular.forEach(copied, function (tax,i) {

                tax.text = tax.taxonomy;
                tax.nodes = _this.getChilds(tax);
                tax.childs = _this.getChilds(tax);

                if( _this.hasChilds(tax.childs) ) {
                    angular.forEach(tax.childs, function (child,i) {
                        _this.taxonomiesThatHasParents.push(child);
                        child.childs = _this.getChilds(child);
                        child.nodes = _this.getChilds(child);
                        child.text = child.taxonomy;
                    });
                }

            });

            return copied;
        },

        hasChilds: function (childs) {
            return (childs.length > 0);
        },

        getChilds: function (taxo) {
            var _this = this;
            var childs = [];
            angular.forEach(_this.taxonomies, function(t,i) {
                if( t.parent == taxo.id ) {
                    childs.push(t);
                }
            });
            return childs;
        },

        isAChild: function (taxo) {
            var _this = this;
            var is = false;
            angular.forEach(_this.taxonomiesThatHasParents, function(parent,i) {
                if( parent.id == taxo.id ) {
                    is = true;
                }
            });
            return is;
        },

        produceTreeView: function () {
            var _this = this;
            angular.forEach(_this.getTaxonomiesWithChildren(), function (t,i) {
                if( ! _this.isAChild(t) ) {
                    _this.tree.push(t);
                }
            });
            return  _this.tree;
        }
    };

    var AncestorsGetter = function AncestorsGetter (id, taxonomies) {
        this.taxId = id;
        this.taxonomies = taxonomies;
        this.ancestors = [];
        this.current = null;
    };
    AncestorsGetter.prototype = {

        getAncestors: function () {
            var _this = this;
            angular.forEach(_this.taxonomies, function (tax, i) {

                var taxIdToBeEvaluated = (_this.current==null) ? _this.taxId : _this.current;

                if( tax.id == taxIdToBeEvaluated ) {
                    if( _this.hasParent(tax) ) {
                        _this.current = tax.parent;
                        _this.ancestors.push(tax.parent);
                        _this.getAncestors();
                    }
                }
            });

            return _this.ancestors;
        },

        hasParent: function (tax) {
            return (tax.parent != null);
        }
    };

    return {

        getTreeView: function (contentTypeTaxonomies) {

            var treeView = new TreeView(contentTypeTaxonomies);

            return treeView.produceTreeView();
        },

        getNodeAncestors: function (id, contentTypeTaxonomies) {

            var AncestorGetter = new AncestorsGetter(id, contentTypeTaxonomies);

            return AncestorGetter.getAncestors();
        }
    }
}]);