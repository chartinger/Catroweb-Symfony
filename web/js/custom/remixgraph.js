String.prototype.trunc = String.prototype.trunc || function(n){ return (this.length > n) ? this.substr(0,n-1)+'...' : this; };

var SCRATCH_PROJECT_BASE_URL = 'https://scratch.mit.edu/projects/';
var SCRATCH_BASE_IMAGE_URL_TEMPLATE = 'https://cdn2.scratch.mit.edu/get_image/project/{}_140x140.png';
var IMAGE_NOT_AVAILABLE_URL = '/images/default/not_available.png';
var CATROBAT_NODE_PREFIX = 'catrobat';
var SCRATCH_NODE_PREFIX = 'scratch';

var RemixGraph = (function () {
    var instance = null;

    return {
        getInstance: function () {
            if (instance == null) {
                instance = new _InternalRemixGraph();
            }
            return instance;
        }
    };
})();

var _InternalRemixGraph = function () {
    var self = this;
    self.programID = 0;
    self.recommendedByPageID = 0;
    self.remixGraphLayerId = null;
    self.clusterIndex = 0;
    self.clusters = [];
    self.lastClusterZoomLevel = 0;
    self.clusterFactor = 0.0;
    self.network = null;
    self.nodes = null;
    self.edges = null;
    self.unavailableNodes = [];
    self.closeButtonSelector = null;
    self.programDetailsUrlTemplate = null;
    self.clickStatisticUrl = null;
    self.remixGraphTranslations = null;

    self.init = function (programID, recommendedByPageID, modalLayerId, remixGraphLayerId, closeButtonClassName,
                          programDetailsUrlTemplate, clickStatisticUrl, remixGraphTranslations) {
        self.reset();
        self.programID = programID;
        self.recommendedByPageID = recommendedByPageID;
        self.remixGraphLayerId = remixGraphLayerId;
        self.closeButtonSelector = $("." + closeButtonClassName);
        self.remixGraphTranslations = remixGraphTranslations;
        self.clickStatisticUrl = clickStatisticUrl;
        self.programDetailsUrlTemplate = programDetailsUrlTemplate;
        $('<div id="context-menu" class="context-menu-trigger" style="display:none;"></div>').appendTo("#" + modalLayerId);
    };

    self.getNodes = function () { return self.nodes; }; // accessed by behat tests
    self.getEdges = function () { return self.edges; }; // accessed by behat tests

    self.reset = function () {
        self.clusterIndex = 0;
        self.clusters = [];
        self.lastClusterZoomLevel = 0;
        self.clusterFactor = 0.9;
        self.network = null;
        self.nodes = new vis.DataSet();
        self.edges = new vis.DataSet();
        self.unavailableNodes = [];
    };

    self.destroy = function () {
        self.reset();

        if (self.network !== null) {
            self.network.destroy();
            self.network = null;
        }
    };

    self.render = function (remixData, loadingAnimation) {
        loadingAnimation.show();
        $("body").css("overflow", "hidden");
        var nodesData = [];
        var edgesData = [];
        var hasGraphCycles = (remixData.remixGraph.catrobatBackwardEdgeRelations.length > 0);

        for (var nodeIndex = 0; nodeIndex < remixData.remixGraph.catrobatNodes.length; ++nodeIndex) {
            var nodeId = parseInt(remixData.remixGraph.catrobatNodes[nodeIndex]);
            var nodeData = {
                id: CATROBAT_NODE_PREFIX + "_" + nodeId,
                //value: (nodeId == remixData.id) ? 3 : 2,
                borderWidth: (nodeId == remixData.id) ? 6 : 3,
                size: (nodeId == remixData.id) ? 60 : 30,
                shape: 'circularImage',
                image: remixData.catrobatProgramThumbnails[nodeId]
            };
            if (nodeId in remixData.remixGraph.catrobatNodesData) {
                var programData = remixData.remixGraph.catrobatNodesData[nodeId];
                nodeData["label"] = programData.name.trunc(15);
                nodeData["name"] = programData.name.trunc(20);
                nodeData["username"] = programData.username;
            } else {
                self.unavailableNodes.push(nodeId);
            }
            nodesData.push(nodeData);
        }

        for (var nodeIndex = 0; nodeIndex < remixData.remixGraph.scratchNodes.length; ++nodeIndex) {
            var nodeId = parseInt(remixData.remixGraph.scratchNodes[nodeIndex]);
            var unavailableProgramData = { name: remixGraphTranslations.programNotAvailable, username: remixGraphTranslations.programUnknownUser };
            var programData = unavailableProgramData;
            var programImageUrl = IMAGE_NOT_AVAILABLE_URL;

            if (nodeId in remixData.remixGraph.scratchNodesData) {
                programData = remixData.remixGraph.scratchNodesData[nodeId];
                programImageUrl = SCRATCH_BASE_IMAGE_URL_TEMPLATE.replace("{}", nodeId);
            }

            nodesData.push({
                id: SCRATCH_NODE_PREFIX + "_" + nodeId,
                label: "[Scratch] " + programData.name.trunc(10),
                name: programData.name.trunc(20),
                username: programData.username,
                shape: 'circularImage',
                image: programImageUrl//,
            });
        }

        for (var edgeIndex = 0; edgeIndex < remixData.remixGraph.catrobatForwardEdgeRelations.length; ++edgeIndex) {
            var edgeData = remixData.remixGraph.catrobatForwardEdgeRelations[edgeIndex];
            edgesData.push({
                from: CATROBAT_NODE_PREFIX + "_" + edgeData.ancestor_id,
                to: CATROBAT_NODE_PREFIX + "_" + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == remixData.id || edgeData.descendant_id == remixData.id) ? 2 : 1
            });
        }

        for (var edgeIndex = 0; edgeIndex < remixData.remixGraph.catrobatBackwardEdgeRelations.length; ++edgeIndex) {
            var edgeData = remixData.remixGraph.catrobatBackwardEdgeRelations[edgeIndex];
            edgesData.push({
                from: CATROBAT_NODE_PREFIX + "_" + edgeData.ancestor_id,
                to: CATROBAT_NODE_PREFIX + "_" + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == remixData.id || edgeData.descendant_id == remixData.id) ? 2 : 1
            });
        }

        for (var edgeIndex = 0; edgeIndex < remixData.remixGraph.scratchEdgeRelations.length; ++edgeIndex) {
            var edgeData = remixData.remixGraph.scratchEdgeRelations[edgeIndex];
            edgesData.push({
                from: SCRATCH_NODE_PREFIX + "_" + edgeData.ancestor_id,
                to: CATROBAT_NODE_PREFIX + "_" + edgeData.descendant_id//,
//            value: (edgeData.ancestor_id == remixData.id || edgeData.descendant_id == remixData.id) ? 2 : 1
            });
        }

        self.nodes.add(nodesData);
        self.edges.add(edgesData);

        var data = { nodes: self.nodes, edges: self.edges };
        var layoutOptions = { improvedLayout: true };

        if (!hasGraphCycles) {
            layoutOptions.hierarchical = {
                parentCentralization: true,
                sortMethod: "directed"
            };
        } else {
            layoutOptions.randomSeed = 42;
        }

        var options = {
            nodes: {
                labelHighlightBold: false,
                borderWidth: 3,
                borderWidthSelected: 3,
                size: 30,
                color: {
                    border: '#CCCCCC',
                    background: '#FFFFFF',
                    highlight: {
                        border: '#CCCCCC'//,
                        //background: '#000000'
                    }
                },
                font: {
                    size: 10,
                    color:'#CCCCCC'//,
//                background: '#FFFFFF'
                },
                shapeProperties: {
                    useBorderWithImage: true
                }
            },
            layout: layoutOptions,
            edges: {
                labelHighlightBold: false,
                color: {
                    color: '#ffffff',
                    highlight: '#ffffff',
                    //highlight: '#000000',
                    hover: '#ffffff',
                    opacity: 1.0
                },
                smooth: {
//            type: 'straightCross'
//            type: 'dynamic'
                    type: 'horizontal'
                },
                arrows: { to: true }
            },
            //smoothCurves: { dynamic:false, type: "continuous" },
            physics:{
                adaptiveTimestep: false,
                stabilization: true
            },
            interaction: {
                dragNodes: false,
                dragView: true,
                hideEdgesOnDrag: true,
                hideNodesOnDrag: false,
                hover: false,
                hoverConnectedEdges: false,
                keyboard: {
                    enabled: true,
                    speed: { x: 20, y: 20, zoom: 0.1 },
                    bindToWindow: true
                },
                multiselect: false,
                navigationButtons: true,
                selectable: true,
                selectConnectedEdges: false,
                zoomView: true
            }
        };
        self.network = new vis.Network(document.getElementById(self.remixGraphLayerId), data, options);
        self.network.setData(data);

        self.nodes.update([{ id: CATROBAT_NODE_PREFIX + "_" + remixData.id, color: { border: '#FFFF00' } }]);

        var clusterOptionsByData = {
            processProperties: function(clusterOptions, childNodes) {
                clusterOptions.label = "[" + childNodes.length + "]";
                return clusterOptions;
            },
            clusterNodeProperties: { borderWidth: 3, shape: 'box', font: { size: 30 } }
        };
        //self.network.clusterByHubsize(undefined, clusterOptionsByData);
        var clusterOptionsByData = {
            /*processProperties: function (clusterOptions, childNodes) {
             clusterIndex = clusterIndex + 1;
             var childrenCount = 0;
             for (var i = 0; i < childNodes.length; i++) {
             childrenCount += childNodes[i].childrenCount || 1;
             }
             clusterOptions.childrenCount = childrenCount;
             clusterOptions.label = "# " + childrenCount + "";
             clusterOptions.font = {size: childrenCount*5+30}
             clusterOptions.id = 'cluster:' + clusterIndex;
             clusters.push({id:'cluster:' + clusterIndex, scale:scale});
             return clusterOptions;
             },*/
            clusterNodeProperties: {borderWidth: 3, shape: 'star', font: {size: 30}}
        };
        //self.network.clusterOutliers(clusterOptionsByData);

        /*
         self.network.on('dragStart', function(event) {
         event.stopPropagation();
         });
         */

        /*
         self.network.on("selectNode", function(params) {
         if (params.nodes.length == 1) {
         if (self.network.isCluster(params.nodes[0]) == true) {
         self.network.openCluster(params.nodes[0]);
         self.network.setOptions({physics:{stabilization:{fit: false}}});
         self.network.stabilize();
         }
         }
         });
         */

        self.network.on("click", self.onClick);
        self.network.on("afterDrawing", function() { loadingAnimation.hide(); });

        /*
         // set the first initial zoom level
         self.network.once('initRedraw', function() {
         if (lastClusterZoomLevel === 0) {
         lastClusterZoomLevel = self.network.getScale();
         }
         });

         // we use the zoom event for our clustering
         self.network.on('zoom', function (params) {
         if (params.direction == '-') {
         if (params.scale < lastClusterZoomLevel*clusterFactor) {
         makeClusters(params.scale);
         lastClusterZoomLevel = params.scale;
         }
         }
         else {
         openClusters(params.scale);
         }
         });

         // if we click on a node, we want to open it up!
         self.network.on("selectNode", function (params) {
         if (params.nodes.length == 1) {
         if (self.network.isCluster(params.nodes[0]) == true) {
         self.network.openCluster(params.nodes[0])
         }
         }
         });
         */
        //clusterById();
        self.network.fit({ animation: false });
    };

    self.clusterById = function () {
        var data = { nodes: self.nodes, edges: self.edges };
        self.network.setData(data);
        var colors = ['orange','lime','DarkViolet'];
        var clusterOptionsByData;
        for (var i = 0; i < colors.length; i++) {
            var color = colors[i];
            clusterOptionsByData = {
                joinCondition: function (childOptions) {
                    var parts = childOptions.id.split("_");
                    if (parts[0] == SCRATCH_NODE_PREFIX) {
                        return false;
                    }
                    return parseInt(parts[1]) >= 100; // the color is fully defined in the node.
                },
                processProperties: function (clusterOptions, childNodes, childEdges) {
                    var totalMass = 0;
                    for (var i = 0; i < childNodes.length; i++) {
                        totalMass += childNodes[i].mass;
                    }
                    clusterOptions.mass = totalMass;
                    return clusterOptions;
                },
                clusterNodeProperties: {
                    id: 'cluster:' + color,
                    borderWidth: 3,
                    shape: 'database',
                    color: color,
                    label: '100 programs'
                }
            };
            self.network.cluster(clusterOptionsByData);
        }
    };

    self.clusterize = function () {
        var clusterOptionsByData = {
            processProperties: function (clusterOptions, childNodes) {
                self.clusterIndex = self.clusterIndex + 1;
                var childrenCount = 0;
                for (var i = 0; i < childNodes.length; i++) {
                    childrenCount += childNodes[i].childrenCount || 1;
                }
                clusterOptions.childrenCount = childrenCount;
                clusterOptions.label = "# " + childrenCount + "";
                clusterOptions.font = {size: childrenCount * 5 + 30}
                clusterOptions.id = 'cluster:' + clusterIndex;
                self.clusters.push({id: 'cluster:' + clusterIndex, scale: scale});
                return clusterOptions;
            },
            clusterNodeProperties: {borderWidth: 3, shape: 'database', font: {size: 30}}
        };
        self.network.clusterOutliers(clusterOptionsByData);
        // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
        self.network.setOptions({ physics: { stabilization: { fit: false }}});
        self.network.stabilize();
    };

    self.onClick = function (params) {
        /*
         if (lastTouchTime != null && (params.event.timeStamp - lastTouchTime) < 1000) {
         params.stopPropagation();
         return;
         }*/

        // prevent multiple simultaneous clicks (needed for Google Chrome on Android)
        var overlayDiv = $("<div></div>").attr("id", "overlay").addClass("overlay");
        overlayDiv.appendTo("body");
        setTimeout("$('#overlay').remove();", 300);

        //lastTouchTime = params.event.timeStamp;
        var selectedNodes = params.nodes;
        self.edges.forEach(function (edgeData) {
            self.edges.update([{ id: edgeData.id, color: { opacity: 1.0 } }]);
        });

        if (selectedNodes.length == 0) {
            return;
        }

        var selectedNodeId = selectedNodes[0];
        var idParts = selectedNodeId.split("_");
        var nodeId = parseInt(idParts[1]);

        if ($.inArray(nodeId, self.unavailableNodes) != -1) {
            // TODO: translate
            //var overlayDiv = $("<div></div>").addClass("overlay");
            //overlayDiv.appendTo("body");
            //sweetAlert("Sorry...", "The program is not available any more!", "error");
            swal({
                    title: self.remixGraphTranslations.programNotAvailableErrorTitle,
                    text: self.remixGraphTranslations.programNotAvailableErrorDescription,
                    type: "error",
                    showCancelButton: false,
                    confirmButtonText: self.remixGraphTranslations.ok,
                    closeOnConfirm: true
                },
                function() {
                    $("#overlay").remove();
                });
            return;
        }

        /*
         var selectedNodeId = selection.nodes[0];
         var newColor = '#' + Math.floor((Math.random() * 255 * 255 * 255)).toString(16);
         self.nodes.update([{
         id: selectedNodeId,
         color: {
         border: '#FF0000',
         background: newColor
         }
         }]);
         */

        var domPosition = params["pointer"]["DOM"];
        var menuWidth = 220;
        var offsetX = (- menuWidth) / 2;
        var selectedNodeData = self.nodes.get(selectedNodeId);
        var selectedEdges = self.network.getConnectedEdges(selectedNodeId);

        /*
         swal({
         title: selectedNodeData["name"],
         text: "by " + selectedNodeData["username"],
         type: "info",
         showCancelButton: false,
         confirmButtonText: "Open",
         showLoaderOnConfirm: true,
         closeOnConfirm: false
         },
         function() {
         closeButtonSelector.click();
         swal("Loading...", "Please wait!", "info");
         var newUrlPrefix = (idParts[0] == CATROBAT_NODE_PREFIX)
         ? detailsUrlTemplate.replace('0', '')
         : SCRATCH_PROJECT_BASE_URL;
         window.location = newUrlPrefix + nodeId;
         }); */

        $.contextMenu('destroy');
        var contextMenuItems = {
            "title": {
                name: "<b>" + selectedNodeData["name"] + "</b>",
                isHtmlName: true,
                className: 'context-menu-item-title context-menu-not-selectable'
            },
            "subtitle": {
                name: self.remixGraphTranslations.by + " " + selectedNodeData["username"],
                isHtmlName: true,
                className: 'context-menu-item-subtitle context-menu-not-selectable'
            }
        };

        if (self.edges.length > 0) {
            contextMenuItems["sep1"] = "---------";
        }

        if (nodeId != self.programID) {
            contextMenuItems["open"] = {
                name: self.remixGraphTranslations.open,
                    icon: "fa-external-link",
                    callback: function () {
                    self.performClickStatisticRequest(nodeId, (idParts[0] != CATROBAT_NODE_PREFIX));
                    self.closeButtonSelector.click();

                    var newUrlPrefix = (idParts[0] == CATROBAT_NODE_PREFIX)
                        ? self.programDetailsUrlTemplate.replace('0', '')
                        : SCRATCH_PROJECT_BASE_URL;

                    var queryString = (idParts[0] == CATROBAT_NODE_PREFIX)
                        ? ("?rec_by_page_id=" + self.recommendedByPageID + "&rec_by_program_id=" + self.programID)
                        : "";
                    window.location = newUrlPrefix + nodeId + queryString;
                }
            };
        }

        if (self.edges.length > 0) {
            contextMenuItems["edges"] = {
                name: self.remixGraphTranslations.showPaths,
                icon: "fa-retweet", // fa-level-down
                callback: function() {
                    self.edges.forEach(function (edgeData) {
                        if (selectedEdges.indexOf(edgeData.id) == -1) {
                            self.edges.update([{ id: edgeData.id, color: { opacity: 0.1 } }]);
                        } else {
                            self.edges.update([{ id: edgeData.id, color: { opacity: 1.0 } }]);
                        }
                    });
                }
            };
        }

        $.contextMenu({
            selector: '.context-menu-trigger',
            trigger: 'left',
            className: 'data-title',
            events: {
                show: function(opt) {
                },
                hide: function(opt) {}
            },
            callback: function(key, options) {
                var m = "clicked: " + key;
                window.console && console.log(m) || alert(m);
            },
            position: function(opt, x, y) {
                var windowWidth = $(window).width();
                if (windowWidth > 1024) {
                    opt.$menu.css({ top: domPosition["y"], left: offsetX + domPosition["x"] });
                } else {
                    var width = Math.max(windowWidth - 100, 200);
                    var height = opt.$menu.css("height").replace("px", "");
                    opt.$menu.css({
                        top: "50%",
                        left: "50%",
                        width: width,
                        maxWidth: width,
                        marginTop: -height/2,
                        marginLeft: -width/2,
                    });
                }
            },
            items: contextMenuItems
        });
        $("#context-menu").click();
    };

    self.performClickStatisticRequest = function (recommendedProgramID, isScratchProgram) {
        var type = "rec_remix_graph";
        var params = { type: type, recFromID: self.programID, recID: recommendedProgramID, isScratchProgram: (isScratchProgram ? 1 : 0) };
        $.ajaxSetup({ async: false });
        $.post(self.clickStatisticUrl, params, function (data) {
            if (data == 'error')
                console.log("No click statistic is created!");
        }).fail(function (data) {
            console.log(data);
        });
    };

    //----------------------------------------------------------------------------------------------------------------------
    self.makeClusters = function (scale) {
        // make the clusters
        var clusterOptionsByData = {
            processProperties: function (clusterOptions, childNodes) {
                clusterIndex = clusterIndex + 1;
                var childrenCount = 0;
                for (var i = 0; i < childNodes.length; i++) {
                    childrenCount += childNodes[i].childrenCount || 1;
                }
                clusterOptions.childrenCount = childrenCount;
                clusterOptions.label = "# " + childrenCount + "";
                clusterOptions.font = {size: childrenCount * 5 + 30}
                clusterOptions.id = 'cluster:' + clusterIndex;
                clusters.push({id: 'cluster:' + clusterIndex, scale: scale});
                return clusterOptions;
            },
            clusterNodeProperties: {borderWidth: 3, shape: 'database', font: {size: 30}}
        };
        self.network.clusterOutliers(clusterOptionsByData);
        // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
        self.network.setOptions({ physics: { stabilization: { fit: false } } });
        self.network.stabilize();
    };

    self.openClusters = function (scale) {
        // open them back up!
        var newClusters = [];
        var declustered = false;
        for (var i = 0; i < clusters.length; i++) {
            if (clusters[i].scale < scale) {
                self.network.openCluster(clusters[i].id);
                self.lastClusterZoomLevel = scale;
                declustered = true;
            }
            else {
                newClusters.push(clusters[i])
            }
        }
        self.clusters = newClusters;
        if (declustered === true) {
            // since we use the scale as a unique identifier, we do NOT want to fit after the stabilization
            self.network.setOptions({physics:{stabilization:{fit: false}}});
            self.network.stabilize();
        }
    };

};
