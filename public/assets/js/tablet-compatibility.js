/**
 * Configuraciones específicas para mejorar la compatibilidad con tablets
 */

// Configuración global de jQuery AJAX para tablets
$(document).ready(function() {
    if (isMobileDevice()) {
        // Configurar timeouts más largos para dispositivos móviles
        $.ajaxSetup({
            timeout: getAjaxTimeout(),
            cache: false
        });
        
        // Configurar headers adicionales para mejor compatibilidad
        $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
            const deviceHeaders = getDeviceSpecificHeaders();
            options.headers = { ...deviceHeaders, ...options.headers };
        });
        
        console.log('Configuración AJAX optimizada para dispositivo móvil activada');
        
        // Configuraciones específicas para iPad
        if (isIPad()) {
            console.log('Configuraciones específicas para iPad aplicadas');
            applyIPadSpecificSettings();
        }
    }
});

/**
 * Configuraciones específicas para iPad
 */
function applyIPadSpecificSettings() {
    // Configurar viewport para iPad
    const viewport = document.querySelector('meta[name=viewport]');
    if (viewport) {
        viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no');
    }
    
    // Optimizar scroll para iPad
    $('body').css({
        '-webkit-overflow-scrolling': 'touch',
        '-webkit-touch-callout': 'none',
        '-webkit-user-select': 'none',
        '-khtml-user-select': 'none',
        '-moz-user-select': 'none',
        '-ms-user-select': 'none',
        'user-select': 'none'
    });
    
    // Configurar eventos táctiles específicos para iPad
    setupIPadTouchEvents();
    
    // Configurar zoom y escalado
    setupIPadZoomSettings();
}

/**
 * Configurar eventos táctiles específicos para iPad
 */
function setupIPadTouchEvents() {
    // Prevenir zoom en doble tap
    let lastTouchEnd = 0;
    document.addEventListener('touchend', function (event) {
        const now = (new Date()).getTime();
        if (now - lastTouchEnd <= 300) {
            event.preventDefault();
        }
        lastTouchEnd = now;
    }, false);
    
    // Prevenir zoom en pinch
    document.addEventListener('gesturestart', function (e) {
        e.preventDefault();
    });
    
    document.addEventListener('gesturechange', function (e) {
        e.preventDefault();
    });
    
    document.addEventListener('gestureend', function (e) {
        e.preventDefault();
    });
}

/**
 * Configurar zoom y escalado para iPad
 */
function setupIPadZoomSettings() {
    // Deshabilitar zoom en inputs
    $('input, textarea, select').css({
        'font-size': '16px' // Evita zoom automático en iOS
    });
    
    // Configurar escalado para diferentes orientaciones
    window.addEventListener('orientationchange', function() {
        setTimeout(function() {
            // Reajustar después del cambio de orientación
            $('body').css('width', '100%');
        }, 100);
    });
}

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
        if (isIPad()) {
            showError("Sin conexión WiFi en iPad. Algunas funciones pueden no estar disponibles.");
        } else {
            showError("Sin conexión a internet. Algunas funciones pueden no estar disponibles.");
        }
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
        
        // Configuraciones específicas para iPad
        if (isIPad()) {
            // Configuraciones adicionales para iPad
            $('body').css({
                '-webkit-text-size-adjust': '100%',
                '-ms-text-size-adjust': '100%'
            });
            
            // Optimizar botones para touch
            $('button, .btn').css({
                'min-height': '44px',
                'min-width': '44px'
            });
        }
        
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
                total: totalMB.toFixed(2) + 'MB',
                device: isIPad() ? 'iPad' : 'Mobile'
            });
            return false;
        }
    }
    return true;
}

/**
 * Función para obtener información del dispositivo
 */
function getDeviceInfo() {
    return {
        isIPad: isIPad(),
        isIOS: isIOSDevice(),
        isMobile: isMobileDevice(),
        userAgent: navigator.userAgent,
        platform: navigator.platform,
        maxTouchPoints: navigator.maxTouchPoints,
        screenWidth: window.screen.width,
        screenHeight: window.screen.height,
        viewportWidth: window.innerWidth,
        viewportHeight: window.innerHeight,
        pixelRatio: window.devicePixelRatio || 1,
        orientation: window.orientation || 'unknown'
    };
}

// Ejecutar optimizaciones al cargar la página
$(window).on('load', function() {
    optimizeForTablet();
    showConnectivityWarning();
    
    // Log información del dispositivo
    console.log('Información del dispositivo:', getDeviceInfo());
    
    // Verificar memoria periódicamente en tablets
    if (isMobileDevice()) {
        setInterval(checkMemoryUsage, 30000); // Cada 30 segundos
    }
});

// Monitorear cambios de conectividad
window.addEventListener('online', function() {
    if (isIPad()) {
        showSuccess("Conexión WiFi restaurada en iPad");
    } else {
        showSuccess("Conexión a internet restaurada");
    }
});

window.addEventListener('offline', function() {
    if (isIPad()) {
        showError("Conexión WiFi perdida en iPad");
    } else {
        showError("Conexión a internet perdida");
    }
});

// Monitorear cambios de orientación en iPad
if (isIPad()) {
    window.addEventListener('orientationchange', function() {
        console.log('Cambio de orientación detectado en iPad');
        setTimeout(function() {
            // Reajustar elementos después del cambio de orientación
            optimizeForTablet();
        }, 500);
    });
} 