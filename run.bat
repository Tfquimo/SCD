@echo off
title SCD Project Starter
echo ===================================================
echo             INICIANDO PROJETO SCD
echo ===================================================

:: Definir caminhos locais do XAMPP
set PHP_PATH=C:\xampp\php\php.exe
set COMPOSER_PATH=C:\xampp\php\composer.phar

:: Verificar se o PHP do XAMPP existe
if not exist "%PHP_PATH%" (
    echo [ERRO] Nao foi possivel encontrar o PHP do XAMPP em: %PHP_PATH%
    echo Por favor, verifique se o XAMPP esta instalado no caminho padrao.
    pause
    exit /b
)

:: Garantir que o ficheiro da base de dados SQLite existe
if not exist "database\database.sqlite" (
    echo [INFO] Criando base de dados SQLite...
    type null > database\database.sqlite
)

:: Verificar/Instalar dependencias do Composer (opcional, caso falte vendor)
if not exist "vendor" (
    echo [INFO] Pasta vendor nao encontrada. Executando composer install...
    if exist "%COMPOSER_PATH%" (
        "%PHP_PATH%" "%COMPOSER_PATH%" install
    ) else (
        echo [AVISO] Composer nao encontrado em %COMPOSER_PATH%. Se as dependencias nao estiverem instaladas, o projeto pode falhar.
    )
)

:: Verificar/Instalar dependencias do Node (pasta node_modules)
if not exist "node_modules" (
    echo [INFO] Pasta node_modules nao encontrada. Executando npm install...
    call npm install
)

:: Executar as migracoes da base de dados
echo [INFO] Executando as migracoes da base de dados...
"%PHP_PATH%" artisan migrate --force

echo ===================================================
echo [SUCESSO] Inicializando os servidores...
echo O backend abrira numa nova janela.
echo O frontend (Vite) abrira numa nova janela.
echo ===================================================

:: Iniciar o Laravel Server (Artisan serve) numa nova janela
start "Laravel Server (Backend)" cmd /k ""%PHP_PATH%" artisan serve"

:: Iniciar o Vite Server (npm run dev) numa nova janela
start "Vite Server (Frontend)" cmd /k "npm run dev"

echo ===================================================
echo Tudo pronto! O site deve estar acessivel em:
echo http://127.0.0.1:8000
echo ===================================================
pause
