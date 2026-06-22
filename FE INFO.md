# 📋 Reglas Básicas para Enviar Comprobantes Externos

## ⚠️ Reglas Críticas (Obligatorias)

### 1. *Campos Requeridos Mínimos*
json
{
  "tipoCom": "01",              // REQUERIDO: "01"=Factura, "02"=Nota Débito, "03"=Nota Crédito, "04"=Tiquete
  "emisor": { "id": 1 },        // REQUERIDO: ID del emisor
  "sucursal": { "id": 1 },      // REQUERIDO: ID de la sucursal
  "condicionVenta": "01",       // REQUERIDO: "01"=Contado, "02"=Crédito
  "medioPago1": "01:1000.00"    // REQUERIDO: Formato "código:monto" (ej: "01:1000.00")
}


### 2. *Condición de Venta y Crédito*
- Si condicionVenta = "02" (Crédito) → *DEBES* enviar plazoCredito con un número mayor a 0
- Si condicionVenta = "01" (Contado) → plazoCredito es opcional

### 3. *Medios de Pago*
- *Formato obligatorio*: "código:monto" (ejemplo: "01:707.00")
- Debe existir *al menos 1 medio de pago* (medioPago1)
- Códigos válidos: "01", "02", "03", "04", "05", "99"
- El monto debe ser un número válido y mayor a 0

*Ejemplos correctos:*
json
"medioPago1": "01:1000.00"    // Efectivo: 1000.00
"medioPago2": "02:500.00"     // Tarjeta: 500.00


*Ejemplos incorrectos:*
json
"medioPago1": "01"            // ❌ Falta el monto
"medioPago1": "1000.00"       // ❌ Falta el código
"medioPago1": "01:0"          // ❌ El monto debe ser > 0


### 4. *Receptor (Si se envía, debe estar completo)*
- Si envías receptorTipoIdentificacion → *DEBES* enviar receptorNumeroIdentificacion
- Si envías receptorNumeroIdentificacion → *DEBES* enviar receptorTipoIdentificacion
- Tipos válidos: "01" (Cédula), "02" (NITE), "03", "04", "05"

### 5. *Líneas de Detalle (Items)*
- *Mínimo 1 línea*, máximo 1000 líneas
- Cada línea debe tener:
  - cantidad > 0 (máximo 3 decimales)
  - unidadMedida válida (ver lista abajo)
  - detalle no vacío (descripción)
  - precioUnitario ≥ 0 (máximo 5 decimales)
  - subTotal = cantidad × precioUnitario - montoDescuento (redondeo a 2 decimales)

*Unidades de medida válidas:*

"Sp", "M", "Kg", "h", "Unid", "Al", "Alc", "Cm", "I", "Os", 
"Spe", "St", "D", "cm", "M2", "M3", "Oz"


### 6. *Código CABYS (Obligatorio por línea)*
- *DEBE* tener exactamente *13 dígitos*
- Solo números (sin letras ni caracteres especiales)
- Ejemplo: "8314300000000"

### 7. *Impuestos (Si el item está gravado)*
- codigoImpuesto: "01" = IVA, "02" = ISC, etc.
- codigoTarifa: "08" = General 13%, "01" = 1%, "02" = 2%, "04" = 4%, "07" = Exento 0%
- tarifaImpuesto: Debe ser 0, 1, 2, 4 o 13 para IVA
- *Cálculo obligatorio*: montoImpuesto = baseImponible × tarifaImpuesto / 100
- Si codigoTarifa = "07" (Exento) → tarifaImpuesto = 0 y montoImpuesto = 0

### 8. *Descuentos*
- Si montoDescuento > 0 → *DEBES* enviar naturlezaDescuento (descripción del descuento)

### 9. *Totales del Comprobante (DEBEN estar correctamente calculados)*
Los totales se validan matemáticamente. Deben cumplir:

- totalVentaResumen = Suma de todos los subTotal de las líneas
- totalDescuentosResumen = Suma de todos los montoDescuento de las líneas
- montoImpuestoResumen = Suma de todos los montoImpuesto de las líneas
- totalComprobanteResumen = totalVentaResumen - totalDescuentosResumen + montoImpuestoResumen + totalOtrosCargosResumen

*Clasificación por CABYS:*
- Si el primer dígito del CABYS es *0-4* → Es *mercancía/bien*
- Si el primer dígito del CABYS es *5-9* → Es *servicio*

Los totales deben clasificarse correctamente:
- totalServGravadosResumen = Suma de servicios con IVA > 0
- totalServExentosResumen = Suma de servicios con IVA = 0
- totalMercanciasGravadasResumen = Suma de bienes con IVA > 0
- totalMercanciasExentasResumen = Suma de bienes con IVA = 0

### 10. *Moneda*
- codigoMonedaResumen: "CRC" o "USD" (default: "CRC")
- Si codigoMonedaResumen ≠ "CRC" → *DEBES* enviar tipoCambioResumen > 0

## ✅ Valores Válidos por Campo

### Condiciones de Venta

"01" = Contado
"02" = Crédito
"03", "04", "05", "06", "99" = Otros


### Medios de Pago

"01" = Efectivo
"02" = Tarjeta
"03" = Cheque
"04" = Transferencia
"05", "99" = Otros


### Tipos de Identificación

"01" = Cédula Física
"02" = Cédula Jurídica (NITE)
"03" = DIMEX
"04" = NITE
"05" = Otro


### Códigos de Tarifa IVA

"08" = General (13%)
"01" = Reducida (1%)
"02" = Reducida (2%)
"04" = Reducida (4%)
"07" = Exento (0%)


## 🚫 Errores Comunes a Evitar

1. ❌ Enviar medioPago1 sin formato "código:monto"
2. ❌ Enviar condicionVenta = "02" sin plazoCredito
3. ❌ Enviar codigoCabys con menos o más de 13 dígitos
4. ❌ Enviar unidadMedida que no esté en la lista válida
5. ❌ Calcular mal los totales (el sistema los valida matemáticamente)
6. ❌ Enviar montoDescuento > 0 sin naturlezaDescuento
7. ❌ Enviar valores negativos en cantidades, precios o totales
8. ❌ Enviar codigoTarifa = "07" (Exento) con montoImpuesto > 0
9. ❌ Enviar montoImpuesto que no coincida con baseImponible × tarifaImpuesto / 100
10. ❌ Enviar moneda diferente a CRC sin tipoCambioResumen

## 📝 Ejemplo Mínimo Válido

json
{
  "tipoCom": "01",
  "emisor": { "id": 1 },
  "sucursal": { "id": 1 },
  "condicionVenta": "01",
  "medioPago1": "01:1130.00",
  "codigoMonedaResumen": "CRC",
  "tipoCambioResumen": 1.0,
  "totalGravadoResumen": 1000.00,
  "totalExentoResumen": 0.00,
  "totalVentaResumen": 1000.00,
  "totalDescuentosResumen": 0.00,
  "totalVentaNetaResumen": 1000.00,
  "montoImpuestoResumen": 130.00,
  "totalComprobanteResumen": 1130.00,
  "totalServGravadosResumen": 1000.00,
  "totalServExentosResumen": 0.00,
  "totalMercanciasGravadasResumen": 0.00,
  "totalMercanciasExentasResumen": 0.00,
  "receptorNombre": "Cliente Ejemplo",
  "receptorTipoIdentificacion": "01",
  "receptorNumeroIdentificacion": "115600276",
  "listaDetalleComprobantes": [
    {
      "numeroLinea": 1,
      "codigoCabys": "8314300000000",
      "cantidad": 1.0,
      "unidadMedida": "Sp",
      "detalle": "Servicio de ejemplo",
      "precioUnitario": 1000.00,
      "montoTotal": 1000.00,
      "subTotal": 1000.00,
      "baseImponible": 1000.00,
      "codigoImpuesto": "01",
      "codigoTarifa": "08",
      "tarifaImpuesto": 13.00,
      "montoImpuesto": 130.00,
      "impuestoNeto": 130.00,
      "montoTotalLinea": 1130.00
    }
  ]
}


## 💡 Tips Importantes

1. *Todos los cálculos se validan*: El sistema verifica que los totales coincidan con las líneas
2. *Tolerancia de redondeo*: Se acepta una diferencia de hasta 0.01 en los cálculos
3. *Decimales*: Respeta los límites de decimales (cantidad: 3, precios/impuestos: 5)
4. *Clasificación automática*: El sistema clasifica automáticamente servicios vs mercancías según el CABYS
5. *Si hay error*: El sistema retorna un JSON con todos los errores encontrados para que puedas corregirlos

## ⚠️ Errores Comunes de Hacienda

### Error -53: "La hora indicada en la emisión del archivo XML no coincide con la hora oficial"

**Causa**: El servidor donde corre la aplicación no está sincronizado con la hora oficial de Costa Rica.

**Solución**:
1. Verificar que el servidor esté sincronizado con NTP (Network Time Protocol)
2. En Windows: Configurar el servicio "Windows Time" para sincronizarse automáticamente
3. En Linux: Verificar que el servicio `ntpd` o `chronyd` esté funcionando
4. Verificar que la zona horaria del servidor esté configurada a `America/Costa_Rica` (UTC-6)

**Verificación en la aplicación**:
- La aplicación Laravel ya está configurada con `timezone => 'America/Costa_Rica'` en `config/app.php`
- El problema está a nivel del sistema operativo del servidor

### Error -99: "La numeración consecutiva [número] del comprobante ya existe en nuestras bases de datos"

**Causa**: El número consecutivo del comprobante ya fue usado anteriormente. Esto puede ocurrir por:
1. Reenvío del mismo comprobante (el usuario intentó enviarlo dos veces)
2. FactuX generó un número consecutivo que ya existe en sus registros
3. Problema en la configuración de consecutivos en FactuX

**Solución**:
1. **No reenviar comprobantes**: Verificar en `fe_info` si el comprobante ya fue enviado antes de intentar enviarlo nuevamente
2. **Verificar con FactuX**: Contactar a FactuX para verificar si hay un problema con la generación de consecutivos
3. **Usar una nueva orden**: Si necesitas enviar el mismo comprobante nuevamente, crear una nueva orden/pago en lugar de reenviar

**Cómo evitar este error**:
- Implementar validación en el frontend para deshabilitar el botón "Enviar" si el comprobante ya fue enviado
- Guardar el estado del comprobante en `fe_info.estado_hacienda` y verificar antes de enviar
- Si el estado es "aceptado" o "rechazado", no permitir reenvío automático

### Otros Errores Comunes

- **Error -1**: Validación XML fallida - Revisar la estructura del JSON enviado
- **Error 500**: Error interno de FactuX - Contactar con soporte de FactuX
- **Error 404**: Endpoint incorrecto - Verificar la URL del endpoint de FactuX

## 📋 Estructura de Respuesta de Hacienda

Cuando consultas el estado de un comprobante, la respuesta puede incluir:

```json
{
  "respuesta": {
    "estadoActual": "ACEPTADO" | "RECHAZADO" | "PENDIENTE",
    "clave": "50602122500011560027600100001040000000905105469477",
    "fechaCreado": "2025-12-02T23:32:41",
    "ultimaActualizacion": "2025-12-02T23:33:02",
    "mensajeRespuestaHacienda": "<?xml version=\"1.0\"...>",
    "codigoEstadoHacienda": "A" | "R" | "P",
    "estadoHacienda": "aceptado" | "rechazado" | "pendiente",
    "fechaEnviadoHacienda": "2025-12-02T23:32:42",
    "fechaRespuestaHacienda": "2025-12-02T23:33:02"
  }
}
```

**Códigos de estado**:
- `"A"` o `"ACEPTADO"` o `"aceptado"` → Comprobante aceptado por Hacienda
- `"R"` o `"RECHAZADO"` o `"rechazado"` → Comprobante rechazado (revisar errores en `mensajeRespuestaHacienda`)
- `"P"` o `"PENDIENTE"` o `"pendiente"` → Comprobante en proceso de validación