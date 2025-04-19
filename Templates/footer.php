</main>
        <footer class="bg-light py-4 mt-4 border-top">
            <div class="container text-center">
                <p class="mb-1 text-muted"><i class="fas fa-university me-2"></i>UNIVERSIDAD DOMINGO SAVIO</p>
                <p class="text-muted small mb-0">&copy; <?php echo date('Y'); ?> Sistema de Empleados. Todos los derechos reservados.<br>Made by Juan Luis Menacho Ram&iacute;rez</p>
            </div>
        </footer>
        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
        
        <!-- DataTable -->
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        
        <script>
            $(document).ready(function() {
                $('.data-table').DataTable({
                    responsive: true,
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay datos disponibles",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                        "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                        "infoFiltered": "(filtrado de _MAX_ registros totales)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrar _MENU_ registros",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "No se encontraron resultados",
                        "paginate": {
                            "first": "Primero",
                            "last": "Último",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        },
                        "aria": {
                            "sortAscending": ": activar para ordenar columna ascendente",
                            "sortDescending": ": activar para ordenar columna descendente"
                        }
                    },
                    dom: "<'dataTables_header_controls'<'row'<'col-sm-6'l><'col-sm-6'f>>>" +
                         "<'row'<'col-sm-12'tr>>" +
                         "<'dataTables_footer_controls'<'row'<'col-sm-5'i><'col-sm-7'p>>>",
                    initComplete: function() {
                        // Reorganizar y mejorar los controles
                        rearrangeDataTableControls();
                    }
                });
                
                // Inicializar tooltips
                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            });
            
            // Función para reorganizar los controles de DataTables
            function rearrangeDataTableControls() {
                $('.dataTables_filter label').contents().filter(function() {
                    return this.nodeType === 3; // Nodo de texto
                }).remove();
                
                $('.dataTables_filter input').attr('placeholder', 'Buscar registros...');
                
                $('.dataTables_length label').contents().filter(function() {
                    return this.nodeType === 3 && this.textContent.trim() !== "";
                }).first().replaceWith('Mostrar ');
                
                $('.dataTables_length label').contents().filter(function() {
                    return this.nodeType === 3 && this.textContent.trim() !== "";
                }).last().replaceWith(' registros');
            }
        </script>
    </body>
</html>