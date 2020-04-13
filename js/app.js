const edgeFilters = document.getElementsByName("edgesFilter");

$(document).ready(function() {
  graphInit();
});

function graphInit() {
  var nodes = [];
  var nodesList = [];
  var selectNodesList = "<option value=''>Todos</option>";
  var edges = [];
  var x = -1000 / 3 - 700 ;
  var y = -650 / 3 + 50;
  var step = 90;
  var DIR_IMG = "images/";
  var DIR_JSON = "json/";
  var EDGE_LENGTH_MAIN = 150;
  var step = 90;

  $.getJSON(DIR_JSON + "groups.json", function(groups) {
    jQuery.each(groups, function(i, group) {
      nodes.push({
        id: group.id,
        x: x,
        y: y + step * i,
        label: group.label,
        group: group.group,
        image: DIR_IMG + group.group + ".png",
        shape: "image",
        fixed: true,
        physics: false
      });

      nodesList.push(
        group.id
      );
    });

    $.getJSON(DIR_JSON + "nodes.json", function(nds) {
      jQuery.each(nds, function(i, node) {
        nodes.push({
          id: node.id,
          label: node.label,
          group: node.group,
          image: "images/" + node.group + ".png",
          shape: "image",
          title: node.ip
        });
        
        nodesList.push(
          node.id
        );
      
      });

      // Defino los valores posibles a filtrar
      var selectbox = $('#nodeFilterSelect');
      selectbox.html(selectNodesList);

      const nodesDataset = new vis.DataSet(nodes);

      $.getJSON(DIR_JSON + "edges.json", function(edgs) {
        jQuery.each(edgs, function(i, edge) {
          edges.push({
            from: edge.from,
            to: edge.to,
            relation: edge.relation,
            arrows: edge.arrows,
            length: EDGE_LENGTH_MAIN,
            color: { color: edge.color }
          });
        });
        const edgesDataset = new vis.DataSet(edges);

        let nodeFilterValue = "";
        let nodesFilterValue = [];

        const edgesFilterValues = {
          cable: true,
          wireless: true
        };

        // esta es una funcion que se aplica cuando se filtra
        const nodesFilter = node => {
         
          if (nodesFilterValue.length === 0) {
            return true;
          }
          if (nodesFilterValue.some(e => e === node.id)) {
            return nodeFilterValue != node.id.toString();
          }

        };

        const edgesFilter = edge => {
          return edgesFilterValues[edge.relation];
        };

        const nodesView = new vis.DataView(nodesDataset, {
          filter: nodesFilter
        });
        const edgesView = new vis.DataView(edgesDataset, {
          filter: edgesFilter
        });

        edgeFilters.forEach(filter =>
          filter.addEventListener("change", e => {
            const { value, checked } = e.target;
            edgesFilterValues[value] = checked;
            edgesView.refresh();
          })
        );

        var data = { nodes: nodesView, edges: edgesView };
        const container = document.getElementById("mynetwork");
        const options = {
          autoResize: true,
          locale: "es",
          interaction: {
            navigationButtons: true,
            keyboard: true
          }
        };
        var network = new vis.Network(container, data, options);
        
        // Evento on double click
        network.on("doubleClick", function (params) {
          params.event = "[original event]";
          // Array con todos los hijos y nietos desde el nodo seleccionado
          children = getChildrenNodesIds(edges, params.nodes.pop());         
          // allNodes - children
          nodesFilterValue = nodesList.filter(x => !children.includes(x));
          nodesView.refresh();
          
        });
      });
    });
  });
}

function getChildrenNodesIds(edges, parent)
{
  var children = [];
  var findChildren = function(parentId){
    edges.forEach(obj => {
      if(obj.from === parentId){
        children.push(obj.to);
        findChildren(obj.to)
      }
    })
  }
  findChildren(parent);
  return children;
}
