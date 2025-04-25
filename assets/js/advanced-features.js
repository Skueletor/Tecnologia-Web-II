document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '/app';
    
    // Initialize all components
    initFormValidation();
    initAutocomplete();
    initDataGrids();
    initRssReader();
    initDownloadManager();
    
    // AJAX Form Validation
    function initFormValidation() {
        const forms = document.querySelectorAll('.ajax-validate');
        
        forms.forEach(form => {
            const fields = form.querySelectorAll('input, select, textarea');
            
            fields.forEach(field => {
                field.addEventListener('blur', function() {
                    validateField(field);
                });
            });
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (validateForm(form)) {
                    const formData = new FormData(form);
                    
                    fetch(`${baseUrl}/api/validation.php`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (form.dataset.redirect) {
                                window.location.href = form.dataset.redirect;
                            } else {
                                showNotification('Formulario enviado correctamente', 'success');
                                form.reset();
                            }
                        } else {
                            Object.entries(data.errors).forEach(([field, message]) => {
                                const fieldElement = form.querySelector(`[name="${field}"]`);
                                showFieldError(fieldElement, message);
                            });
                            
                            showNotification('Por favor corrija los errores', 'error');
                        }
                    })
                    .catch(error => {
                        showNotification('Error al procesar el formulario', 'error');
                    });
                }
            });
        });
        
        function validateField(field) {
            const value = field.value.trim();
            let isValid = true;
            let errorMessage = '';
            
            // Required validation
            if (field.hasAttribute('required') && value === '') {
                isValid = false;
                errorMessage = 'Este campo es obligatorio';
            }
            
            // Email validation
            if (field.type === 'email' && value !== '' && !validateEmail(value)) {
                isValid = false;
                errorMessage = 'Ingrese un correo electrónico válido';
            }
            
            // Min length validation
            if (field.dataset.minlength && value.length < parseInt(field.dataset.minlength)) {
                isValid = false;
                errorMessage = `Debe tener al menos ${field.dataset.minlength} caracteres`;
            }
            
            // Custom pattern validation
            if (field.pattern && value !== '') {
                const pattern = new RegExp(field.pattern);
                if (!pattern.test(value)) {
                    isValid = false;
                    errorMessage = field.dataset.errorPattern || 'Formato inválido';
                }
            }
            
            if (!isValid) {
                showFieldError(field, errorMessage);
            } else {
                clearFieldError(field);
            }
            
            return isValid;
        }
        
        function validateForm(form) {
            const fields = form.querySelectorAll('input, select, textarea');
            let isValid = true;
            
            fields.forEach(field => {
                if (!validateField(field)) {
                    isValid = false;
                }
            });
            
            return isValid;
        }
        
        function showFieldError(field, message) {
            clearFieldError(field);
            
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            
            field.parentNode.appendChild(errorDiv);
        }
        
        function clearFieldError(field) {
            field.classList.remove('is-invalid');
            const errorDiv = field.parentNode.querySelector('.invalid-feedback');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
        
        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
    }
    
    // Autocomplete
    function initAutocomplete() {
        const autocompleteFields = document.querySelectorAll('.autocomplete');
        
        autocompleteFields.forEach(field => {
            const wrapper = document.createElement('div');
            wrapper.className = 'autocomplete-wrapper position-relative';
            
            field.parentNode.insertBefore(wrapper, field);
            wrapper.appendChild(field);
            
            const suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'autocomplete-suggestions d-none';
            wrapper.appendChild(suggestionsContainer);
            
            let debounceTimer;
            
            field.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                
                debounceTimer = setTimeout(() => {
                    const query = this.value.trim();
                    
                    if (query.length < 2) {
                        suggestionsContainer.classList.add('d-none');
                        suggestionsContainer.innerHTML = '';
                        return;
                    }
                    
                    fetch(`${baseUrl}/api/autocomplete.php?field=${field.dataset.field}&query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            suggestionsContainer.innerHTML = '';
                            
                            if (data.length > 0) {
                                suggestionsContainer.classList.remove('d-none');
                                
                                data.forEach(item => {
                                    const suggestion = document.createElement('div');
                                    suggestion.className = 'autocomplete-item p-2';
                                    suggestion.textContent = item.text;
                                    
                                    suggestion.addEventListener('click', () => {
                                        field.value = item.text;
                                        
                                        if (field.dataset.valueField) {
                                            const valueInput = document.querySelector(`#${field.dataset.valueField}`);
                                            if (valueInput) {
                                                valueInput.value = item.value;
                                            }
                                        }
                                        
                                        suggestionsContainer.classList.add('d-none');
                                        field.focus();
                                    });
                                    
                                    suggestionsContainer.appendChild(suggestion);
                                });
                            } else {
                                suggestionsContainer.classList.add('d-none');
                            }
                        })
                        .catch(() => {
                            suggestionsContainer.classList.add('d-none');
                        });
                }, 300);
            });
            
            document.addEventListener('click', function(e) {
                if (!wrapper.contains(e.target)) {
                    suggestionsContainer.classList.add('d-none');
                }
            });
        });
    }
    
    // Data Grids
    function initDataGrids() {
        const grids = document.querySelectorAll('.ajax-grid');
        
        grids.forEach(grid => {
            const gridId = grid.id || 'grid-' + Math.random().toString(36).substr(2, 9);
            grid.id = gridId;
            
            const endpoint = grid.dataset.endpoint;
            const pageSize = parseInt(grid.dataset.pageSize) || 10;
            
            const headerRow = document.createElement('div');
            headerRow.className = 'grid-header-row';
            
            const bodyContainer = document.createElement('div');
            bodyContainer.className = 'grid-body';
            
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'grid-pagination d-flex justify-content-between align-items-center mt-3';
            
            grid.appendChild(headerRow);
            grid.appendChild(bodyContainer);
            grid.appendChild(paginationContainer);
            
            const gridState = {
                page: 1,
                pageSize: pageSize,
                sortField: grid.dataset.sortField || 'id',
                sortDirection: grid.dataset.sortDirection || 'asc',
                filters: {}
            };
            
            function loadGridData() {
                bodyContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div></div>';
                
                const queryParams = new URLSearchParams();
                queryParams.append('page', gridState.page);
                queryParams.append('pageSize', gridState.pageSize);
                queryParams.append('sortField', gridState.sortField);
                queryParams.append('sortDirection', gridState.sortDirection);
                
                Object.entries(gridState.filters).forEach(([field, value]) => {
                    queryParams.append(`filter[${field}]`, value);
                });
                
                fetch(`${baseUrl}/api/${endpoint}?${queryParams.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        renderGrid(data);
                    })
                    .catch(error => {
                        bodyContainer.innerHTML = '<div class="alert alert-danger m-3">Error al cargar los datos</div>';
                    });
            }
            
            function renderGrid(data) {
                if (grid.querySelectorAll('.grid-header-cell').length === 0) {
                    renderHeaderRow(data.columns);
                }
                
                renderBodyRows(data.rows, data.columns);
                renderPagination(data.totalRows);
            }
            
            function renderHeaderRow(columns) {
                headerRow.innerHTML = '';
                
                columns.forEach(column => {
                    const headerCell = document.createElement('div');
                    headerCell.className = 'grid-header-cell';
                    
                    if (column.sortable) {
                        headerCell.classList.add('sortable');
                        headerCell.addEventListener('click', () => {
                            if (gridState.sortField === column.field) {
                                gridState.sortDirection = gridState.sortDirection === 'asc' ? 'desc' : 'asc';
                            } else {
                                gridState.sortField = column.field;
                                gridState.sortDirection = 'asc';
                            }
                            
                            loadGridData();
                        });
                    }
                    
                    headerCell.innerHTML = `
                        ${column.title} 
                        ${column.sortable ? `<i class="fas ${gridState.sortField === column.field ? 
                            (gridState.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down') : 
                            'fa-sort'}"></i>` : ''}
                    `;
                    
                    if (column.width) {
                        headerCell.style.width = column.width;
                    }
                    
                    headerRow.appendChild(headerCell);
                });
            }
            
            function renderBodyRows(rows, columns) {
                bodyContainer.innerHTML = '';
                
                if (rows.length === 0) {
                    bodyContainer.innerHTML = '<div class="grid-empty-state p-4 text-center text-muted">No hay datos disponibles</div>';
                    return;
                }
                
                rows.forEach(row => {
                    const rowElement = document.createElement('div');
                    rowElement.className = 'grid-row';
                    
                    if (row.id) {
                        rowElement.dataset.id = row.id;
                    }
                    
                    columns.forEach(column => {
                        const cell = document.createElement('div');
                        cell.className = 'grid-cell';
                        
                        if (column.render) {
                            cell.innerHTML = column.render(row[column.field], row);
                        } else {
                            cell.textContent = row[column.field] || '';
                        }
                        
                        rowElement.appendChild(cell);
                    });
                    
                    bodyContainer.appendChild(rowElement);
                });
            }
            
            function renderPagination(totalRows) {
                const totalPages = Math.ceil(totalRows / gridState.pageSize);
                
                let paginationHTML = `
                    <div class="showing-info">
                        Mostrando ${((gridState.page - 1) * gridState.pageSize) + 1} a 
                        ${Math.min(gridState.page * gridState.pageSize, totalRows)} de ${totalRows} registros
                    </div>
                    <ul class="pagination mb-0">
                        <li class="page-item ${gridState.page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="1"><i class="fas fa-angle-double-left"></i></a>
                        </li>
                        <li class="page-item ${gridState.page === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${gridState.page - 1}"><i class="fas fa-angle-left"></i></a>
                        </li>
                `;
                
                let startPage = Math.max(1, gridState.page - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                
                if (endPage - startPage < 4 && startPage > 1) {
                    startPage = Math.max(1, endPage - 4);
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    paginationHTML += `
                        <li class="page-item ${i === gridState.page ? 'active' : ''}">
                            <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>
                    `;
                }
                
                paginationHTML += `
                        <li class="page-item ${gridState.page === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${gridState.page + 1}"><i class="fas fa-angle-right"></i></a>
                        </li>
                        <li class="page-item ${gridState.page === totalPages ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-page="${totalPages}"><i class="fas fa-angle-double-right"></i></a>
                        </li>
                    </ul>
                `;
                
                paginationContainer.innerHTML = paginationHTML;
                
                paginationContainer.querySelectorAll('.page-link').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const page = parseInt(this.dataset.page);
                        if (page !== gridState.page && page > 0 && page <= totalPages) {
                            gridState.page = page;
                            loadGridData();
                        }
                    });
                });
            }
            
            loadGridData();
        });
    }
    
    // RSS Reader
    function initRssReader() {
        const rssContainers = document.querySelectorAll('.rss-feed');
        
        rssContainers.forEach(container => {
            const feedUrl = container.dataset.feedUrl;
            const maxItems = parseInt(container.dataset.maxItems) || 5;
            
            if (feedUrl) {
                container.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div></div>';
                
                fetch(`${baseUrl}/api/rss-proxy.php?url=${encodeURIComponent(feedUrl)}&max=${maxItems}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            container.innerHTML = `<div class="alert alert-danger">Error: ${data.error}</div>`;
                            return;
                        }
                        
                        let feedHTML = '';
                        
                        if (data.title) {
                            feedHTML += `<h4 class="rss-title mb-3">${data.title}</h4>`;
                        }
                        
                        if (data.items && data.items.length > 0) {
                            feedHTML += '<div class="rss-items">';
                            
                            data.items.forEach(item => {
                                const pubDate = item.pubDate ? new Date(item.pubDate).toLocaleDateString() : '';
                                
                                feedHTML += `
                                    <div class="rss-item mb-3">
                                        <h5 class="rss-item-title mb-1">
                                            <a href="${item.link}" target="_blank">${item.title}</a>
                                        </h5>
                                        ${pubDate ? `<div class="rss-item-date text-muted small mb-1">${pubDate}</div>` : ''}
                                        ${item.description ? `<div class="rss-item-desc">${item.description}</div>` : ''}
                                    </div>
                                `;
                            });
                            
                            feedHTML += '</div>';
                        } else {
                            feedHTML += '<div class="alert alert-info">No hay elementos para mostrar</div>';
                        }
                        
                        container.innerHTML = feedHTML;
                    })
                    .catch(error => {
                        container.innerHTML = '<div class="alert alert-danger">Error al cargar el feed RSS</div>';
                    });
            }
        });
    }
    
    // Download Manager
    function initDownloadManager() {
        const downloadButtons = document.querySelectorAll('.ajax-download');
        
        downloadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const downloadUrl = button.getAttribute('href') || button.dataset.url;
                const downloadType = button.dataset.downloadType || 'file';
                
                if (!downloadUrl) return;
                
                button.disabled = true;
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparando descarga...';
                
                fetch(downloadUrl)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }
                        
                        if (downloadType === 'blob') {
                            return response.blob();
                        } else {
                            return response.json();
                        }
                    })
                    .then(data => {
                        button.disabled = false;
                        button.innerHTML = originalText;
                        
                        if (downloadType === 'blob') {
                            const url = window.URL.createObjectURL(data);
                            const filename = button.dataset.filename || 'downloaded-file';
                            
                            const a = document.createElement('a');
                            a.style.display = 'none';
                            a.href = url;
                            a.download = filename;
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                        } else {
                            if (data.downloadUrl) {
                                window.location.href = data.downloadUrl;
                            } else if (data.error) {
                                showNotification(data.error, 'error');
                            }
                        }
                    })
                    .catch(error => {
                        button.disabled = false;
                        button.innerHTML = originalText;
                        showNotification('Error al procesar la descarga', 'error');
                    });
            });
        });
    }
    
    // Utility Functions
    function showNotification(message, type = 'info') {
        const existingNotification = document.querySelector('.toast-notification');
        if (existingNotification) {
            existingNotification.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `toast-notification toast-${type}`;
        notification.innerHTML = `
            <div class="toast-icon">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
            </div>
            <div class="toast-message">${message}</div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        notification.querySelector('.toast-close').addEventListener('click', () => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        });
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    }
});
