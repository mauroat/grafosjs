<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Grafos JS</title>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <script type="text/javascript" src="js/jquery.min.js"></script>`
        <script src="js/bootstrap.min.js"></script>
        <script src="js/vis-network.b.min.js"></script>
        <style type="text/css">
            #mynetwork {
                width: 1000px;
                height: 650px;
                border: 1px solid lightgray;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen",
                "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue",
                sans-serif;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }

            code {
                font-family: source-code-pro, Menlo, Monaco, Consolas, "Courier New", monospace;
            }
        </style>
        <?php
        header("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, X-Token-Auth, Authorization");
        ?>
    </head>
    <body>
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h1>Grafos JS</h1>
            </div>
            <div class="card-body">
                <div class="row">
                    <ul>
                        <li>Aristas con flechas</li>
                        <li>Tooltip con IP al posar mouse sobre nodo</li>
                        <li>Controles de navegación</li>
                        <li>Filtro dinámico para nodos y relaciones</li>
                    </ul>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5>Filtrar por nodos</h5>
                        <div class="input-group">
                            <select id='nodeFilterSelect' class="form-control">
                                <option value=''>Todos</option>
                                <option value='modem'>Módems</option>
                                <option value='router'>Routers</option>
                                <option value='firewall'>Firewalls</option>
                                <option value='balancer'>Balanceadores de carga</option>
                                <option value='server'>Servidor físicos</option>
                                <option value='vserver'>Servidor virtuales</option>
                                <option value='docker'>Dockers</option>
                                <option value='database'>Bases de datos</option>
                                <option value='switch'>Switches</option>
                                <option value='ws'>Webservices</option>
                                <option value='host'>Hosts</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Filtrar por tipo de conexión</h5>
                        <div class="input-group">
                            <div class="col-md-3">
                                <label>
                                    <input type='checkbox' name='edgesFilter' value='cable' class="form-control"
                                           checked></input>
                                    <span style="color:blue">Cable</span>
                                </label>
                            </div>
                            <div class="col-md-3">
                                <label>
                                    <input type='checkbox' name='edgesFilter' value='wireless' class="form-control"
                                           checked></input>
                                    <span style="color:red">Wireless</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <br><br>
                </div>
                <div class="row">
                    <div id="mynetwork"></div>
                </div>
            </div>
        </div>
    </div>
    </body>
    <script type="text/javascript">
        const nodeFilterSelector = document.getElementById('nodeFilterSelect')
        const edgeFilters = document.getElementsByName('edgesFilter')

        $(document).ready(function () {
            setNodesAndEdges();
        });

        function startNetwork(data) {
            const container = document.getElementById('mynetwork')
            const options = {
                autoResize: true,
                locale: 'es',
                interaction: {
                    navigationButtons: true,
                    keyboard: true
                },
            }
            new vis.Network(container, data, options)


        }

        function setNodesAndEdges() {
            var nodes = [];
            var edges = [];
            var x = -1000 / 2 + 50;
            var y = -650 / 2 + 50;
            var step = 90;
            var DIR = 'images/';
            var EDGE_LENGTH_MAIN = 150;
            var EDGE_LENGTH_SUB = 50;
            var step = 70;
            var arrow_types = [
                'arrow'
            ];
            $.getJSON("json/groups.json", function (groups) {
                jQuery.each(groups, function (i, group) {
                    nodes.push({
                        id: group.id,
                        x: x,
                        y: y + step * i,
                        label: group.label,
                        group: group.group,
                        image: "images/" + group.group + ".png",
                        shape: "image",
                        fixed: true,
                        physics: false
                    });
                });
                $.getJSON("json/nodes.json", function (nds) {
                    jQuery.each(nds, function (i, node) {
                        nodes.push({
                            id: node.id,
                            label: node.label,
                            group: node.group,
                            image: "images/" + node.group + ".png",
                            shape: "image",
                            title: node.ip, age: 'kid', gender: 'male'
                        });
                    });
                    const nodesDataset = new vis.DataSet(nodes);

                    $.getJSON("json/edges.json", function (edgs) {
                        jQuery.each(edgs, function (i, edge) {
                            edges.push({
                                from: edge.from,
                                to: edge.to,
                                relation: edge.relation,
                                arrows: edge.arrows,
                                length: EDGE_LENGTH_MAIN,
                                color: {color: edge.color}
                            });
                        });
                        const edgesDataset = new vis.DataSet(edges);

                        /**
                         * filter values are updated in the outer scope.
                         * in order to apply filters to new values, DataView.refresh() should be called
                         */
                        let nodeFilterValue = ''
                        const edgesFilterValues = {
                            cable: true,
                            wireless: true
                        }

                        /*
                          filter function should return true or false
                          based on whether item in DataView satisfies a given condition.
                        */
                        const nodesFilter = (node) => {
                            if (nodeFilterValue === '') {
                                return true
                            }
                            switch (nodeFilterValue) {
                                case('modem'):
                                    return node.group === 'modem'
                                case('router'):
                                    return node.group === 'router'
                                case('firewall'):
                                    return node.group === 'firewall'
                                case('balancer'):
                                    return node.group === 'balancer'
                                case('server'):
                                    return node.group === 'server'
                                case('vserver'):
                                    return node.group === 'vserver'
                                case('docker'):
                                    return node.group === 'docker'
                                case('database'):
                                    return node.group === 'database'
                                case('ws'):
                                    return node.group === 'ws'
                                case('switch'):
                                    return node.group === 'switch'
                                case('host'):
                                    return node.group === 'host'
                                default:
                                    return true
                            }
                        }

                        const edgesFilter = (edge) => {
                            return edgesFilterValues[edge.relation]
                        }

                        const nodesView = new vis.DataView(nodesDataset, {filter: nodesFilter})
                        const edgesView = new vis.DataView(edgesDataset, {filter: edgesFilter})


                        nodeFilterSelector.addEventListener('change', (e) => {
                            // set new value to filter variable
                            nodeFilterValue = e.target.value
                            /*
                              refresh DataView,
                              so that its filter function is re-calculated with the new variable
                            */
                            nodesView.refresh()
                        })

                        edgeFilters.forEach(filter => filter.addEventListener('change', (e) => {
                            const {value, checked} = e.target
                            edgesFilterValues[value] = checked
                            edgesView.refresh()
                        }))

                        startNetwork({nodes: nodesView, edges: edgesView})
                    });

                });
            });
        }
    </script>
</html>
