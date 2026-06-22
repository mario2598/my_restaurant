# 📄 Documentación - Sistema de Facturación Electrónica

## 🎯 Descripción General

Este sistema permite enviar facturas electrónicas a Hacienda de Costa Rica de forma automática, cumpliendo con todos los reglamentos vigentes de facturación electrónica.

## 🏗️ Arquitectura de la Solución

### **1. Base de Datos**

#### Tablas Existentes:
- `orden`: Contiene las órdenes generadas
- `pago_orden`: **Contiene cada pago/factura** (una orden puede tener múltiples pagos)
- `detalle_orden`: Líneas de productos de cada orden
- `detalle_pago_orden`: **Líneas específicas de cada pago/factura**
- `fe_info`: Información específica para facturación electrónica (relacionada con `pago_orden`)
- `producto_fe_info`: Configuración FE de productos (CABYS, unidad medida)
- `cliente_fe_info`: Información FE de clientes
- `sucursal`: Datos del emisor (tu restaurante)

#### ⚠️ **Importante: Relación Orden → Pago → Factura**

Una **orden** puede tener **múltiples pagos**, y cada **pago** genera una **factura electrónica** independiente. Esto permite:
- Dividir la cuenta entre varias personas
- Hacer pagos parciales
- Generar varias facturas electrónicas para una misma orden

```
1 Orden → N Pagos → N Facturas Electrónicas
```

### **2. Flujo del Proceso**

```
1. Usuario genera una orden → Se crea registro en `orden`
2. Usuario realiza un pago → Se crea registro en `pago_orden` + `detalle_pago_orden`
3. Se solicita FE → Se crea registro en `fe_info` (estado: PENDIENTE, con id_pago)
4. Usuario hace clic en "Enviar a Hacienda" → Se valida y construye JSON del PAGO
5. Se envía a API → Hacienda procesa el comprobante
6. Se recibe respuesta → Se actualiza estado a ENVIADA + clave numérica
```

## 📋 Componentes Implementados

### **Backend (PHP - FeController.php)**

#### Métodos Principales:

1. **`enviarFacturaHacienda()`**
   - Orquesta todo el proceso de envío
   - Valida datos antes de enviar
   - Actualiza el estado en BD

2. **`construirComprobanteElectronico()`**
   - Construye el JSON según especificaciones de Hacienda
   - Incluye: Emisor, Receptor, Detalles, Resumen, etc.

3. **`generarClaveNumerica()`**
   - Genera la clave de 50 dígitos
   - Estructura: País + Fecha + Cédula + Consecutivo + Código Seguridad

4. **`construirDetalles()`**
   - Construye las líneas de detalle
   - Incluye: CABYS, cantidad, precios, impuestos, descuentos

5. **`validarProductosFE()`**
   - Verifica que todos los productos tengan configuración FE
   - Lista productos faltantes si hay errores

6. **`enviarAHacienda()`**
   - Hace la petición HTTP a la API de facturación
   - Maneja respuesta y errores

7. **`obtenerJsonComprobante()`**
   - Genera el JSON sin enviar (para debugging)
   - Útil para validar antes de enviar

### **Frontend (JavaScript - facturas.js)**

#### Funciones Principales:

1. **`enviarFacturaHacienda(idOrden, idInfoFe)`**
   - Botón principal de envío
   - Muestra confirmación antes de enviar
   - Loading mientras procesa
   - Muestra resultado (éxito o error)

2. **`verJsonComprobante(idOrden, idInfoFe)`**
   - Muestra el JSON que se enviará a Hacienda
   - Permite copiar al portapapeles
   - Útil para debugging

3. **`abrirModalEnvia()` / `enviarOrden()`**
   - Método manual para marcar como enviada
   - Para casos especiales

### **Vista (Blade - facturas.blade.php)**

La vista muestra 3 botones para cada factura pendiente:

1. **Botón Azul (Enviar a Hacienda)**: Envío automático
2. **Botón Info (Ver JSON)**: Para debugging
3. **Botón Verde (Marcar manual)**: Método antiguo (backup)

## 🔧 Estructura del JSON - Comprobante Electrónico

```json
{
  "clave": "50621012024000301234567890001000010000000111234567",
  "consecutivo": "2024-PZ-1",
  "fechaEmision": "2024-10-21T14:30:00-06:00",
  
  "emisor": {
    "nombre": "Mi Restaurante S.A.",
    "identificacion": {
      "tipo": "02",
      "numero": "3101234567"
    },
    "nombreComercial": "Sucursal Centro",
    "ubicacion": {
      "provincia": "1",
      "canton": "01",
      "distrito": "01",
      "barrio": "01",
      "otrasSenas": "Dirección del local"
    },
    "telefono": {
      "codigoPais": "506",
      "numTelefono": "22223333"
    },
    "correoElectronico": "facturacion@mirestaurante.com"
  },
  
  "receptor": {
    "nombre": "Cliente Ejemplo",
    "identificacion": {
      "tipo": "01",
      "numero": "109870543"
    },
    "correoElectronico": "cliente@email.com"
  },
  
  "condicionVenta": "01",
  "medioPago": "01",
  
  "detalleServicio": [
    {
      "numeroLinea": 1,
      "codigo": {
        "tipo": "04",
        "codigo": "5020301010000"
      },
      "cantidad": 2,
      "unidadMedida": "Unid",
      "detalle": "Café Americano",
      "precioUnitario": "1500.00000",
      "montoTotal": "3000.00000",
      "subtotal": "3000.00000",
      "impuesto": {
        "codigo": "01",
        "codigoTarifa": "08",
        "tarifa": "13.00",
        "monto": "390.00000"
      },
      "montoTotalLinea": "3390.00000"
    }
  ],
  
  "resumenFactura": {
    "codigoMoneda": "CRC",
    "tipoCambio": 1,
    "totalServGravados": "3000.00000",
    "totalGravado": "3000.00000",
    "totalVenta": "3000.00000",
    "totalDescuentos": "0.00000",
    "totalVentaNeta": "3000.00000",
    "totalImpuesto": "390.00000",
    "totalComprobante": "3390.00000"
  }
}
```

## 📝 Códigos y Catálogos de Hacienda

### **Tipos de Identificación:**
- `01`: Cédula Física (9 dígitos)
- `02`: Cédula Jurídica (10 dígitos)
- `03`: DIMEX (11-12 dígitos)
- `04`: NITE

### **Condiciones de Venta:**
- `01`: Contado
- `02`: Crédito
- `03`: Consignación
- `04`: Apartado
- `05`: Arrendamiento
- `06`: Arrendamiento con opción de compra
- `07`: Arrendamiento en función financiera
- `99`: Otros

### **Medios de Pago:**
- `01`: Efectivo
- `02`: Tarjeta
- `03`: Cheque
- `04`: Transferencia / Depósito / SINPE
- `05`: Recaudado por terceros
- `99`: Otros

### **Códigos de Impuesto:**
- `01`: IVA (Impuesto al Valor Agregado)
- `02`: Selectivo de consumo
- `03`: Único a los combustibles
- `04`: Específico de bebidas alcohólicas
- `05`: Específico sobre bebidas envasadas
- `06`: A los productos de tabaco
- `07`: Sobre el valor agregado por ventas de tabaco
- `99`: Otros

### **Tarifas de IVA:**
- `08`: Tarifa general 13%
- `01`: Tarifa 0% (Exento)
- `02`: Tarifa 1%
- `03`: Tarifa 2%
- `04`: Tarifa 4%

### **Unidades de Medida Comunes:**
- `Unid`: Unidad
- `Sp`: Servicio Profesional
- `m`: Metro
- `kg`: Kilogramo
- `Lt`: Litro
- `Oz`: Onza
- `g`: Gramo
- `ml`: Mililitro

### **Tipo de Código Producto:**
- `01`: Código del producto del vendedor
- `02`: Código del producto del comprador
- `03`: Código del producto asignado por la industria
- `04`: Código uso interno (por defecto)
- `99`: Otros

## 🚀 Pasos para Implementación

### **Paso 1: Configurar Variables de Entorno**

Agrega en tu archivo `.env`:

```env
# API de Facturación Electrónica
API_FE_URL=https://www.stage.spacesoftwarecr.com/ElectricPosWs/wsPos
```

### **Paso 2: Configurar Información del Emisor**

Actualiza la tabla `sucursal` con tu información:

```sql
UPDATE sucursal SET 
    nombre_factura = 'Tu Empresa S.A.',
    cedula_factura = '3101234567',
    correo_factura = 'facturacion@tuempresa.com'
WHERE id = 1;
```

### **Paso 3: Configurar Productos con Información FE**

Para cada producto, debes configurar:

1. **Código CABYS**: Busca en https://www.hacienda.go.cr/cabys
2. **Unidad de Medida**: Según catálogo de Hacienda
3. **Tarifa de Impuesto**: Generalmente 13%

Ejemplo:

```sql
INSERT INTO producto_fe_info (
    codigo_producto, 
    tipo_producto, 
    codigo_cabys, 
    unidad_medida, 
    tarifa_impuesto,
    tipo_codigo
) VALUES (
    'CAFE-001',
    'MENU',
    '5020301010000',
    'Unid',
    13.00,
    '04'
);
```

### **Paso 4: Pruebas**

1. **Ver JSON del Comprobante:**
   - Haz clic en el botón azul (ojo) para ver el JSON
   - Verifica que todos los datos sean correctos
   - Copia el JSON si necesitas analizarlo

2. **Enviar a Hacienda:**
   - Haz clic en el botón azul (avión)
   - Confirma el envío
   - Espera la respuesta

3. **Verificar Estado:**
   - Si fue exitoso, verás la clave numérica
   - El estado cambiará a "Enviada"

## ⚠️ Validaciones Implementadas

El sistema valida:

1. ✅ Que la orden no esté anulada
2. ✅ Que todos los productos tengan código CABYS
3. ✅ Que todos los productos tengan unidad de medida
4. ✅ Que exista información del emisor
5. ✅ Que exista información del receptor
6. ✅ Formato correcto de identificaciones
7. ✅ Cálculos correctos de impuestos y totales

## 🐛 Debugging

### **Ver JSON sin Enviar:**

```javascript
// En la consola del navegador
verJsonComprobante(idOrden, idInfoFe);
```

### **Revisar Errores Comunes:**

1. **"Producto sin código CABYS"**
   - Configura el producto en `producto_fe_info`
   - Busca el código en https://www.hacienda.go.cr/cabys

2. **"Error al enviar a Hacienda"**
   - Verifica la URL de la API
   - Verifica las credenciales
   - Revisa los logs del servidor

3. **"Clave numérica inválida"**
   - Verifica que la cédula del emisor sea correcta
   - Verifica el formato de fecha

## 📊 Estructura de la Clave Numérica (50 dígitos)

```
506 21 10 24 000301234567 001 00001 01 0000000111 1 23456789
│   │  │  │  │            │   │     │  │          │ │
│   │  │  │  │            │   │     │  │          │ └─ Código Seguridad (8)
│   │  │  │  │            │   │     │  │          └─── Situación (1)
│   │  │  │  │            │   │     │  └────────────── Consecutivo (10)
│   │  │  │  │            │   │     └───────────────── Tipo Documento (2)
│   │  │  │  │            │   └─────────────────────── Terminal (5)
│   │  │  │  │            └─────────────────────────── Sucursal (3)
│   │  │  │  └──────────────────────────────────────── Cédula Jurídica (12)
│   │  │  └─────────────────────────────────────────── Año (2)
│   │  └────────────────────────────────────────────── Mes (2)
│   └───────────────────────────────────────────────── Día (2)
└───────────────────────────────────────────────────── País (3)
```

## 🔐 Seguridad

- El sistema valida permisos antes de enviar
- Solo usuarios con permiso `fe_fes` pueden acceder
- Se registra todo en la tabla `fe_info`
- Se mantiene auditoría de envíos

## 📞 Soporte

Para más información sobre facturación electrónica en Costa Rica:
- Portal de Hacienda: https://www.hacienda.go.cr
- Catálogo CABYS: https://www.hacienda.go.cr/cabys
- Documentación técnica: https://www.hacienda.go.cr/ATV/Login.aspx

---

**Desarrollado con ❤️ para Mi Restaurante**

