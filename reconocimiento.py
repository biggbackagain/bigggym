import cv2
import face_recognition

print("Cargando base de datos biométrica...")

# 1. Cargamos tu foto de referencia y extraemos tu "firma matemática"
foto_referencia = face_recognition.load_image_file("bryan.jpeg")
vector_bryan = face_recognition.face_encodings(foto_referencia)[0]

# 2. Encendemos la cámara
cap = cv2.VideoCapture(0)
print("Cámara iniciada. Mostrando video en vivo... (Presiona 'q' para salir)")

while True:
    ret, frame = cap.read()
    if not ret:
        break

    # Optimizador: Reducimos el tamaño del video a un 25% para que el análisis sea instantáneo
    small_frame = cv2.resize(frame, (0, 0), fx=0.25, fy=0.25)
    # Convertimos los colores (OpenCV usa BGR, face_recognition usa RGB)
    rgb_small_frame = cv2.cvtColor(small_frame, cv2.COLOR_BGR2RGB)

    # El motor busca todas las caras en el video actual y saca sus vectores
    face_locations = face_recognition.face_locations(rgb_small_frame)
    face_encodings = face_recognition.face_encodings(rgb_small_frame, face_locations)

    # Analizamos cada cara que haya aparecido en la cámara
    for (top, right, bottom, left), face_encoding in zip(face_locations, face_encodings):
        
        # Comparamos la cara detectada con tu firma matemática guardada (tolerancia estándar de 0.6)
        coincidencias = face_recognition.compare_faces([vector_bryan], face_encoding, tolerance=0.6)
        
        # Por defecto, asumimos que es alguien que no ha pagado (Rojo)
        nombre = "Desconocido (Sin Acceso)"
        color = (0, 0, 255) 

        # Si el motor dice "Sí, el vector coincide con el de Bryan"
        if True in coincidencias:
            nombre = "Socio: Bryan Garcia (ACTIVO)"
            color = (0, 255, 0) # Lo pintamos de Verde

        # Regresamos las coordenadas a su tamaño original (porque las achicamos al 25%)
        top *= 4
        right *= 4
        bottom *= 4
        left *= 4

        # Dibujamos el rectángulo y el texto
        cv2.rectangle(frame, (left, top), (right, bottom), color, 2)
        cv2.putText(frame, nombre, (left, top - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.8, color, 2)

    # Mostramos la pantalla final
    cv2.imshow('BiggGym - Control de Acceso', frame)

    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()