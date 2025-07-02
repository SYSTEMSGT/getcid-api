GET https://api.systems.gt/getcid-api/getcid

Parámetros requeridos
| Parámetro | Tipo | Obligatorio | Descripción | 
| token | string | ✅ Sí | Token asignado al cliente | 
| iid | string | ✅ Sí | ID único de instalación | 

🧪 Ejemplo de solicitud
GET https://api.systems.gt/getcid-api/getcid?token=abc12345xyz&iid=9876543210

💾 Respuesta
{
  "cid": "111111-111111-111111-111111-111111-111111-111111-111111",
  "c_cid": "111111111111111111111111111111111111111111111111"
}

🔁 Códigos de respuesta
| Código | Descripción | 
| 200 | OK – Solicitud exitosa | 
| 400 | Solicitud mal formada o incompleta | 
| 401 | Token inválido o no autorizado | 
| 500 | Error interno del servidor |
