from fastapi import FastAPI, HTTPException
from sqlalchemy import create_engine, Column, Integer, String, ForeignKey, Double, SmallInteger, Boolean
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, relationship
from sqlalchemy import create_engine
from databases import Database
from pydantic import BaseModel
import httpx
from fastapi.middleware.cors import CORSMiddleware

# Configuración de la base de datos MySQL
DATABASE_URL = "mysql+mysqlconnector://aaf49d_quiosco:rmskk2020@MYSQL5035.site4now.net/db_aaf49d_quiosco"
engine = create_engine(DATABASE_URL)

try:
    with engine.connect() as connection:
        print("Conexión exitosa")
except Exception as e:
    print(f"Error en la conexión: {e}")

# Declarative base para el ORM de SQLAlchemy
Base = declarative_base()

class UpdateSubscriptionRequest(BaseModel):
    active: bool

# Modelos de la base de datos

class Usuario(Base):
    __tablename__ = "usuarios"

    id = Column(Integer, primary_key=True, autoincrement=True)
    nombre = Column(String(50))
    apellido = Column(String(50))
    correo = Column(String(50))

class Subscripcion(Base):
    __tablename__ = "subscripciones"

    id = Column(Integer, ForeignKey('usuarios.id'), primary_key=True)
    sub = Column(String(50), primary_key=True)
    estatus = Column(SmallInteger)

    usuario = relationship("Usuario", back_populates="subscripciones")

Usuario.subscripciones = relationship("Subscripcion", back_populates="usuario")

class Plan(Base):
    __tablename__ = "planes"
    sub = Column(String(50), primary_key=True)
    costo = Column(Double)

class ContenidoUser(Base):
    __tablename__ = "contenido_user"
    usuario_id = Column(Integer, ForeignKey('usuarios.id'), primary_key=True)
    sub_status = Column(SmallInteger, ForeignKey('subscripciones.estatus'), primary_key=True)
    show_cont = Column(Boolean)

# Crear un motor y sessionmaker
engine = create_engine(DATABASE_URL)
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

# Crear las tablas en la base de datos
Base.metadata.create_all(bind=engine)

# URL del webhook (puedes cambiarla según sea necesario)
WEBHOOK_URL = "http://localhost:5000/webhook"  # Cambia esta URL por la del webhook real

# Función para enviar eventos al webhook
async def send_webhook_event(event_type: str, data: dict):
    """
    Enviar un evento al webhook.
    :param event_type: Tipo de evento (e.g., 'subscription_created', 'subscription_updated', etc.).
    :param data: Datos relacionados con el evento.
    """
    async with httpx.AsyncClient() as client:
        try:
            response = await client.post(WEBHOOK_URL, json={"event": event_type, "data": data})
            response.raise_for_status()
        except httpx.HTTPError as e:
            # Manejar errores de comunicación con el webhook
            print(f"Error al enviar el webhook: {e}")

# Funciones para interactuar con la base de datos MySQL

# Función para crear una suscripción y obtener el nombre del usuario
async def create_subscription_in_db(user_id: int, title: str):
    db = SessionLocal()
    try:
        # Verificar si el usuario existe
        usuario = db.query(Usuario).filter(Usuario.id == user_id).first()
        if not usuario:
            raise HTTPException(status_code=404, detail="Usuario no encontrado")

        # Crear nueva suscripción
        new_subscription = Subscripcion(id=user_id, sub=title, estatus=1)  # 1 es activo por defecto
        db.add(new_subscription)
        db.commit()
        db.refresh(new_subscription)
        
        # Obtener el nombre del usuario
        user_name = usuario.nombre

        # Enviar el evento de suscripción creada con el nombre del usuario
        await send_webhook_event("subscription_created", {"user_id": user_id, "title": title, "username": user_name})
        
        return new_subscription
    finally:
        db.close()

async def get_subscriptions_from_db(user_id: int):
    db = SessionLocal()
    try:
        # Obtener suscripciones del usuario
        subscriptions = db.query(Subscripcion).filter(Subscripcion.id == user_id).all()
        return subscriptions
    finally:
        db.close()

async def update_subscription_in_db(subscription_id: int, active: bool):
    db = SessionLocal()
    try:
        # Actualizar suscripción
        subscription = db.query(Subscripcion).filter(Subscripcion.id == subscription_id).first()
        if not subscription:
            raise HTTPException(status_code=404, detail="Suscripción no encontrada")

        subscription.estatus = 1 if active else 0
        db.commit()
        db.refresh(subscription)
        return subscription
    finally:
        db.close()

async def delete_subscription_in_db(subscription_id: int):
    db = SessionLocal()
    try:
        # Eliminar suscripción
        subscription = db.query(Subscripcion).filter(Subscripcion.id == subscription_id).first()
        if not subscription:
            raise HTTPException(status_code=404, detail="Suscripción no encontrada")

        db.delete(subscription)
        db.commit()
        return {"detail": "Suscripción eliminada"}
    finally:
        db.close()

# FastAPI app
app = FastAPI()

# Agregar middleware para permitir CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Permite solicitudes desde cualquier origen
    allow_credentials=True,
    allow_methods=["*"],  # Permite cualquier método (GET, POST, PUT, DELETE)
    allow_headers=["*"],  # Permite cualquier encabezado
)

class CreateSubscriptionRequest(BaseModel):
    user_id: int
    title: str


# Endpoint para crear una suscripción
@app.post("/subscriptions/")
async def create_subscription(user_id: int, title: str):
    # Crear suscripción en la base de datos
    new_subscription = await create_subscription_in_db(user_id, title)
    # Enviar evento al webhook
    await send_webhook_event("subscription_created", {"user_id": user_id, "title": title})
    return {"subscription": new_subscription}

# Endpoint para obtener todas las suscripciones
@app.get("/subscriptions/")
async def read_subscriptions(user_id: int, skip: int = 0, limit: int = 10):
    subscriptions = await get_subscriptions_from_db(user_id)
    return subscriptions[skip:skip + limit]

# Endpoint para activar o desactivar una suscripción
@app.put("/subscriptions/{subscription_id}")
async def update_subscription(subscription_id: int, request: UpdateSubscriptionRequest):
    try:
        updated_subscription = await update_subscription_in_db(subscription_id, request.active)
        # Enviar evento al webhook
        await send_webhook_event(
            "subscription_updated",
            {"subscription_id": subscription_id, "active": request.active}
        )
        return {"updated_subscription": updated_subscription}
    except HTTPException as e:
        raise e

# Endpoint para eliminar una suscripción
@app.delete("/subscriptions/{subscription_id}")
async def delete_subscription(subscription_id: int):
    try:
        db_response = await delete_subscription_in_db(subscription_id)
        # Enviar evento al webhook
        await send_webhook_event("subscription_deleted", {"subscription_id": subscription_id})
        return {"db_response": db_response}
    except HTTPException as e:
        raise e
