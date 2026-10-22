# 📱 Guia de Compilação Mobile (Capacitor / Android APK) — FAMFIN

Este documento detalha como gerar e compilar o **FAMFIN** para **Mobile (Android APK via Capacitor)**.

---

## 📱 1. Configuração do Capacitor

A aplicação está configurada no ficheiro `capacitor.config.json` apontando para o servidor de produção:
- **URL**: `http://146.235.224.99/famfin/`
- **App ID**: `com.famfin.app`
- **App Name**: `FAMFIN`
- **Splash Screen & Status Bar**: Tema Dark (`#020617`) com destaque em Emerald (`#10b981`).

---

## 🛠️ 2. Pré-requisitos no Sistema

- **Node.js** >= 18
- **Java JDK** 21 (Temurin / OpenJDK)
- **Android SDK** (instalado em `~/Android/Sdk` ou configurado em `local.properties`)

---

## 🚀 3. Comandos para Sincronizar e Compilar

### A. Sincronizar Recursos Web
```bash
npx cap sync android
```

### B. Compilar APK Diretamente via Linha de Comando (Gradle)
```bash
cd android
./gradlew assembleDebug
```
O ficheiro APK gerado estará localizado em:
`android/app/build/outputs/apk/debug/app-debug.apk`

### C. Abrir no Android Studio
```bash
npx cap open android
```
No Android Studio, clique em **Build** > **Build Bundle(s) / APK(s)** > **Build APK(s)**.

---

## 🎨 4. Recursos Mobile Ativos
- **Bottom Navigation Bar**: Barra de navegação inferior estilo nativo (`md:hidden`) com acesso rápido a:
  - Início (Dashboard)
  - Transações
  - Botão Central Elevado (+) para Nova Transação
  - Orçamentos
  - Menu Completo
- **Safe Area Insets**: Compatibilidade total com entalhes/notches (`viewport-fit=cover`, `env(safe-area-inset-bottom)`).
- **Gaveta Lateral Deslizante**: Menu completo off-canvas acessível no mobile com backdrop suave.
- **Splash Screen Integrada**: Transição suave ao abrir o aplicativo.
