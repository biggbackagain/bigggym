import cv2

# Cargamos el modelo matemático pre-entrenado de OpenCV para detectar rostros frontales
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

# Encendemos la cámara (el índice 0 suele ser la cámara web principal)
cap = cv2.VideoCapture(0)

print("Iniciando cámara... Presiona la tecla 'q' en tu teclado para salir.")

while True:
    # Capturamos el video cuadro por cuadro
    ret, frame = cap.read()
    if not ret:
        print("Error al leer la cámara.")
        break

    # Convertimos el cuadro a escala de grises (las redes neuronales procesan esto mucho más rápido)
    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

    # El motor busca rostros en la imagen gris
    faces = face_cascade.detectMultiScale(gray, scaleFactor=1.3, minNeighbors=5)

    # Por cada rostro detectado, dibujamos un rectángulo verde en la imagen a color
    for (x, y, w, h) in faces:
        cv2.rectangle(frame, (x, y), (x+w, y+h), (0, 255, 0), 3)
        cv2.putText(frame, 'Socio Detectado', (x, y - 10), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (0, 255, 0), 2)

    # Mostramos la ventana con el resultado en vivo
    cv2.imshow('BiggGym - Prueba de Ojo Biometrico', frame)

    # Si se presiona la letra 'q', rompemos el ciclo
    if cv2.waitKey(1) & 0xFF == ord('q'):
        break

# Al terminar, liberamos la cámara y cerramos la ventana
cap.release()
cv2.destroyAllWindows()