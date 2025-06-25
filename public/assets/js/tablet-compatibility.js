/**
 * Configuraciones específicas para mejorar la compatibilidad con tablets
 */

// Configuración global de jQuery AJAX para tablets
$(document).ready(function() {
    if (isMobileDevice()) {
        // Configurar timeouts más largos para dispositivos móviles
        $.ajaxSetup({
            timeout: 30000,
            cache: false
        });
        
        // Configurar headers adicionales para mejor compatibilidad
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            options.headers = options.headers || {};
            options.headers['X-Device-Type'] = 'mobile';
            options.headers['X-Requested-With'] = 'XMLHttpRequest';
        });
        
        console.log('Configuración AJAX optimizada para dispositivo móvil activada');
    }
});

/**
 * Función para verificar la conectividad de red
 */
function checkNetworkConnectivity() {
    return navigator.onLine;
}

/**
 * Función para mostrar advertencia de conectividad
 */
function showConnectivityWarning() {
    if (!checkNetworkConnectivity()) {
        showError("Sin conexión a internet. Algunas funciones pueden no estar disponibles.");
    }
}

/**
 * Función para reintentar operaciones fallidas
 */
function retryOperation(operation, maxRetries = 3, delay = 1000) {
    let retryCount = 0;
    
    function attempt() {
        return operation().catch(error => {
            retryCount++;
            if (retryCount < maxRetries) {
                console.log(`Reintentando operación (${retryCount}/${maxRetries})...`);
                return new Promise(resolve => {
                    setTimeout(() => resolve(attempt()), delay * retryCount);
                });
            }
            throw error;
        });
    }
    
    return attempt();
}

/**
 * Función para optimizar el rendimiento en tablets
 */
function optimizeForTablet() {
    if (isMobileDevice()) {
        // Reducir animaciones para mejor rendimiento
        $('*').css({
            'animation-duration': '0.2s',
            'transition-duration': '0.2s'
        });
        
        // Optimizar scroll para mejor rendimiento
        $('.scrollable').css({
            '-webkit-overflow-scrolling': 'touch'
        });
        
        console.log('Optimizaciones para tablet aplicadas');
    }
}

/**
 * Función para detectar problemas de memoria
 */
function checkMemoryUsage() {
    if (isMobileDevice() && 'memory' in performance) {
        const memory = performance.memory;
        const usedMB = memory.usedJSHeapSize / 1048576;
        const totalMB = memory.totalJSHeapSize / 1048576;
        
        if (usedMB > totalMB * 0.8) {
            console.warn('Uso de memoria alto en dispositivo móvil:', {
                used: usedMB.toFixed(2) + 'MB',
                total: totalMB.toFixed(2) + 'MB'
            });
            return false;
        }
    }
    return true;
}

// Ejecutar optimizaciones al cargar la página
$(window).on('load', function() {
    optimizeForTablet();
    showConnectivityWarning();
    
    // Verificar memoria periódicamente en tablets
    if (isMobileDevice()) {
        setInterval(checkMemoryUsage, 30000); // Cada 30 segundos
    }
});

// Monitorear cambios de conectividad
window.addEventListener('online', function() {
    showSuccess("Conexión a internet restaurada");
});

window.addEventListener('offline', function() {
    showError("Conexión a internet perdida");
}); 