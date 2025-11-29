# GETCID API - SYSTEMSGT

## Parámetros requeridos
| Parámetro | Tipo | Obligatorio | Descripción | 
| ------- | ---- | -------- | -------- |
| token | string | Sí | Token asignado al cliente | 
| iid | int | Sí | ID único de instalación (54 a 63 digitos, sin espacios ni guiones, solo nuúmeros) | 

## Ejemplo de solicitud
**GET** https://api.systems.gt/getcid-api/getcid?token=abc12345xyz&iid=9876543210

## Uso con Postman
Puedes usar Postman para probar esta API. Crea una colección con variables:
- **token**: Tu token privado
- **iid**: El identificador de instalación
- Usa esta URL en tus requests:
<pre>https://api.systems.gt/getcid-api/getcid?token={{token}}&iid={{iid}}</pre>


## Respuesta JSON
<pre>{
  "iid": "111111-111111-111111-111111-111111-111111-111111-111111",
  "c_cid": "111111111111111111111111111111111111111111111111"
}</pre>

## Códigos de respuesta
| Código | Descripción |
| ------- | ---- |
| 200 | OK – Solicitud exitosa | 
| 400 | Solicitud mal formada o incompleta | 
| 401 | Token no autorizado | 
| 404 | Not Found | 
| 500 | Error interno del servidor |

### Notas adicionales
> [!CAUTION]
> Protege tu token y evitar exponerlo públicamente.
