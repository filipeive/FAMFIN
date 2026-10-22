#!/usr/bin/env bash
set -e

echo "============================================="
echo "   FAMFIN — Compilação de APK Android"
echo "============================================="

# Detect Java 21 JDK
if [ -d "/home/fdev-ms/.jdks/jbr-21.0.11" ]; then
    export JAVA_HOME="/home/fdev-ms/.jdks/jbr-21.0.11"
elif [ -n "$JAVA_HOME" ]; then
    echo "Usando JAVA_HOME existente: $JAVA_HOME"
fi

export PATH="$JAVA_HOME/bin:$PATH"
export ANDROID_HOME="${ANDROID_HOME:-/home/fdev-ms/Android/Sdk}"

echo "==> 1. Compilar assets do Vite..."
npm run build

echo "==> 2. Sincronizar com o Capacitor Android..."
npx cap sync android

echo "==> 3. Compilar APK Debug via Gradle..."
cd android
./gradlew assembleDebug

cd ..
cp android/app/build/outputs/apk/debug/app-debug.apk FAMFIN-v1.0.apk

echo "============================================="
echo "✅ APK gerado com sucesso!"
echo "Localização: $(pwd)/FAMFIN-v1.0.apk"
echo "Tamanho: $(ls -lh FAMFIN-v1.0.apk | awk '{print $5}')"
echo "============================================="
