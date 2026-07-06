<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SCD — Segurança de Dados">
    <title>SCD - Segurança de Dados</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --brand-green: #409b68;
            --brand-green-dark: #327a51;
            --brand-green-light: #e8f5ed;
            --text-dark: #1a202c;
            --text-muted: #718096;
            --bg-light: #fefefe;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            margin: 0;
            scroll-behavior: smooth;
        }

        /* Navbar */
        .navbar-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 5%;
            background: rgba(254,254,254,0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.03);
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: .7rem;
            text-decoration: none;
            color: var(--text-dark);
        }

        .brand-icon {
            width: 32px; height: 32px;
            background: var(--brand-green);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .brand-text {
            font-weight: 800;
            font-size: 1.2rem;
            line-height: 1.1;
        }
        .brand-subtext {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            font-size: .95rem;
            position: relative;
        }

        .nav-links a.active {
            color: var(--brand-green);
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 100%;
            height: 2px;
            background: var(--brand-green);
            border-radius: 2px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-outline {
            border: 1px solid #e2e8f0;
            background: transparent;
            color: var(--text-dark);
            padding: .5rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
        }
        .btn-outline:hover { background: #f8fafc; color: var(--text-dark); }

        .btn-green {
            background: var(--brand-green);
            color: white;
            padding: .6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all .2s;
            border: none;
        }
        .btn-green:hover { background: var(--brand-green-dark); color: white; }

        /* Hero Section */
        .hero {
            padding: 10rem 5% 5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 4rem;
            min-height: 80vh;
        }

        .hero-content {
            flex: 1;
            max-width: 600px;
        }

        .badge-sec {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: var(--brand-green-light);
            color: var(--brand-green-dark);
            padding: .4rem .8rem;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            color: var(--text-dark);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }

        .hero h1 span {
            color: var(--brand-green);
        }

        .hero p {
            font-size: 1.15rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 4rem;
        }

        .btn-lg-green {
            background: var(--brand-green);
            color: white;
            padding: .8rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.05rem;
        }
        .btn-lg-green:hover { color:white; background: var(--brand-green-dark); }

        .btn-lg-outline {
            background: white;
            color: var(--text-dark);
            border: 1px solid #e2e8f0;
            padding: .8rem 1.8rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: 1.05rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .btn-lg-outline i { color: var(--brand-green); }

        .hero-features {
            display: flex;
            gap: 2rem;
            border-top: 1px solid #edf2f7;
            padding-top: 2rem;
        }

        .hf-item {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .hf-item i {
            color: var(--brand-green);
            font-size: 1.2rem;
        }
        .hf-text strong {
            display: block;
            font-size: .85rem;
            color: var(--text-dark);
        }
        .hf-text span {
            font-size: .75rem;
            color: var(--text-muted);
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            position: relative;
        }
        
        .hero-image img {
            max-width: 100%;
            width: 550px;
            height: auto;
            transform: scale(1.05);
        }

        /* Features Section */
        .features-section {
            padding: 5rem 5%;
            background: #f8fafc;
            text-align: center;
        }

        .section-subtitle {
            color: var(--brand-green);
            font-weight: 700;
            font-size: .85rem;
            letter-spacing: .1em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 4rem;
        }
        .section-title span { color: var(--brand-green); }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 3rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: left;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            transition: transform .3s;
        }
        .feature-card:hover { transform: translateY(-5px); }

        .fc-icon {
            width: 44px; height: 44px;
            background: var(--brand-green-light);
            color: var(--brand-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
        }

        .fc-title {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: .5rem;
            color: var(--text-dark);
        }

        .fc-desc {
            font-size: .85rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .features-footer {
            background: white;
            display: inline-flex;
            gap: 3rem;
            padding: 1rem 2.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .ff-item {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-size: .85rem;
            color: var(--text-muted);
        }
        .ff-item i { color: var(--brand-green); font-size: 1.1rem; }
        .ff-item strong { color: var(--text-dark); }

        /* Generic Section Template */
        .generic-section {
            padding: 6rem 5%;
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }

        /* ── Responsive Mobile ── */
        @media (max-width: 991.98px) {
            .hero {
                flex-direction: column;
                padding-top: 8rem;
                text-align: center;
                gap: 2rem;
            }
            .hero-content {
                max-width: 100%;
            }
            .hero h1 {
                font-size: 2.8rem;
            }
            .hero-buttons {
                justify-content: center;
                flex-wrap: wrap;
            }
            .hero-features {
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
            }
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .features-footer {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1.5rem;
            }
            .nav-links {
                display: none; /* Hide standard nav links on mobile for simplicity, or we could add a hamburger */
            }
            #como-funciona > div {
                flex-direction: column;
            }
            .hero-image img {
                width: 100%;
                max-width: 400px;
                transform: scale(1);
            }
        }

        @media (max-width: 575.98px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            .hero-buttons {
                flex-direction: column;
                width: 100%;
            }
            .hero-buttons a {
                width: 100%;
                justify-content: center;
            }
            .features-grid {
                grid-template-columns: 1fr;
            }
            .generic-section {
                padding: 4rem 5%;
            }
            .section-title {
                font-size: 1.8rem;
            }
        }

    </style>
</head>
<body>

    <nav class="navbar-custom">
        <a href="#inicio" class="brand-logo">
            <div class="brand-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <div>
                <div class="brand-text">SCD</div>
                <div class="brand-subtext">Segurança de Dados</div>
            </div>
        </a>

        <div class="nav-links">
            <a href="#inicio" class="active">Início</a>
            <a href="#recursos">Recursos</a>
            <a href="#como-funciona">Como funciona</a>
            <a href="#sobre">Sobre</a>
        </div>

        <div class="nav-actions">
            <i class="bi bi-moon" style="font-size:1.1rem;color:var(--text-dark);cursor:pointer;margin-right:.5rem;"></i>
            <a href="{{ route('login') }}" class="btn-green">Entrar</a>
        </div>
    </nav>

    <!-- INÍCIO -->
    <section id="inicio" class="hero">
        <div class="hero-content">
            <div class="badge-sec">
                <i class="bi bi-lock-fill"></i> Segurança Militar AES-256
            </div>
            <h1>Proteja o que importa.<br><span>Criptografe com confiança.</span></h1>
            <p>Criptografe seus arquivos com AES-256 de nível militar.<br>Privacidade total, desempenho máximo.</p>
            
            <div class="hero-buttons">
                <a href="{{ route('login') }}" class="btn-lg-green">Começar Agora <i class="bi bi-arrow-right"></i></a>
                <a href="#recursos" class="btn-lg-outline"><i class="bi bi-play-circle"></i> Ver Demonstração</a>
            </div>

            <div class="hero-features">
                <div class="hf-item">
                    <i class="bi bi-shield-check"></i>
                    <div class="hf-text">
                        <strong>Criptografia AES-256</strong>
                        <span>Padrão militar</span>
                    </div>
                </div>
                <div class="hf-item">
                    <i class="bi bi-lock"></i>
                    <div class="hf-text">
                        <strong>Privacidade Total</strong>
                        <span>Seus dados, só seus</span>
                    </div>
                </div>
                <div class="hf-item">
                    <i class="bi bi-lightning-charge"></i>
                    <div class="hf-text">
                        <strong>Alta Performance</strong>
                        <span>Rápido e eficiente</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/green_folder.png') }}" alt="3D Folder Security Illustration">
        </div>
    </section>

    <!-- RECURSOS -->
    <section id="recursos" class="features-section">
        <div class="section-subtitle">RECURSOS PRINCIPAIS</div>
        <h2 class="section-title">Tudo que você precisa para<br>manter seus dados <span>seguros.</span></h2>

        <div class="features-grid">
            <div class="feature-card">
                <div class="fc-icon"><i class="bi bi-shield-check"></i></div>
                <div class="fc-title">Criptografia Forte</div>
                <div class="fc-desc">Algoritmo AES-256 de nível militar para máxima segurança.</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon"><i class="bi bi-key"></i></div>
                <div class="fc-title">Gerenciamento de Chaves</div>
                <div class="fc-desc">Suas chaves, seu controle. Ninguém mais tem acesso.</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon"><i class="bi bi-file-earmark-text"></i></div>
                <div class="fc-title">Vários Formatos</div>
                <div class="fc-desc">Suporte a diversos tipos de arquivos e tamanhos.</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon"><i class="bi bi-cloud-slash"></i></div>
                <div class="fc-title">Sem Armazenamento</div>
                <div class="fc-desc">Não armazenamos seus arquivos. Tudo acontece localmente.</div>
            </div>
        </div>

        <div class="features-footer">
            <div class="ff-item">
                <i class="bi bi-lock-fill"></i>
                <div><strong>100% Seguro</strong> Seus dados nunca são armazenados em nossos servidores.</div>
            </div>
            <div class="ff-item">
                <i class="bi bi-check-circle-fill"></i>
                <div><strong>Conformidade</strong> Em conformidade com as melhores práticas de segurança.</div>
            </div>
        </div>
    </section>

    <!-- COMO FUNCIONA -->
    <section id="como-funciona" class="generic-section" style="background: white;">
        <div class="section-subtitle">COMO FUNCIONA</div>
        <h2 class="section-title" style="margin-bottom: 2rem;">Simples. Rápido. <span>Seguro.</span></h2>
        <p style="color:var(--text-muted);margin-bottom:4rem;">A encriptação não tem de ser complicada. Em três passos o seu ficheiro fica blindado contra qualquer interceção.</p>
        
        <div style="display:flex;gap:2rem;text-align:left;">
            <div style="flex:1;padding:2rem;border:1px solid #edf2f7;border-radius:12px;">
                <div style="font-size:2rem;color:var(--brand-green);font-weight:800;margin-bottom:1rem;">1.</div>
                <h4 style="font-weight:700;font-size:1.1rem;">Escolha o Ficheiro</h4>
                <p style="font-size:.9rem;color:var(--text-muted);">Selecione qualquer documento, imagem ou base de dados. Não há restrição de formato.</p>
            </div>
            <div style="flex:1;padding:2rem;border:1px solid #edf2f7;border-radius:12px;background:var(--brand-green);color:white;">
                <div style="font-size:2rem;color:var(--brand-green-light);font-weight:800;margin-bottom:1rem;">2.</div>
                <h4 style="font-weight:700;font-size:1.1rem;color:white;">Criptografia AES-256</h4>
                <p style="font-size:.9rem;color:rgba(255,255,255,0.8);">O algoritmo militar tranca a informação em microsegundos com uma chave única.</p>
            </div>
            <div style="flex:1;padding:2rem;border:1px solid #edf2f7;border-radius:12px;">
                <div style="font-size:2rem;color:var(--brand-green);font-weight:800;margin-bottom:1rem;">3.</div>
                <h4 style="font-weight:700;font-size:1.1rem;">Partilhe em Segurança</h4>
                <p style="font-size:.9rem;color:var(--text-muted);">Decida quem tem acesso. Revogue as permissões a qualquer momento com um clique.</p>
            </div>
        </div>
    </section>


    <!-- SOBRE -->
    <section id="sobre" class="generic-section" style="background: white;">
        <div class="section-subtitle">A NOSSA MISSÃO</div>
        <h2 class="section-title">A privacidade não é um luxo.<br>É um <span>direito fundamental.</span></h2>
        <p style="color:var(--text-muted);font-size:1.1rem;line-height:1.7;max-width:800px;margin:0 auto 2rem;">
            O SCD foi desenvolvido com uma premissa muito simples: os dados da sua empresa pertencem à sua empresa. Não existem "backdoors", não existem acessos ocultos. A matemática por detrás do AES-256 garante que ninguém, nem mesmo os administradores do servidor, consegue ver os seus ficheiros sem a chave de desencriptação.
        </p>
        <a href="{{ route('about') }}" class="btn-outline">Ler o Manifesto Completo</a>
    </section>

    <!-- FOOTER -->
    <footer style="background:var(--text-dark);color:white;padding:3rem 5%;text-align:center;">
        <div class="brand-logo" style="color:white;justify-content:center;margin-bottom:1rem;">
            <div class="brand-icon"><i class="bi bi-shield-lock-fill"></i></div>
            <div class="brand-text">SCD</div>
        </div>
        <p style="color:#a0aec0;font-size:.85rem;">© 2026 Sistema de Criptografia de Dados. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Lógica simples para destacar o link de navegação conforme o scroll
        const sections = document.querySelectorAll("section[id]");
        window.addEventListener("scroll", navHighlighter);

        function navHighlighter() {
            let scrollY = window.pageYOffset;
            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 100;
                const sectionId = current.getAttribute("id");
                
                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    document.querySelector(".nav-links a[href*=" + sectionId + "]").classList.add("active");
                } else {
                    document.querySelector(".nav-links a[href*=" + sectionId + "]").classList.remove("active");
                }
            });
        }
    </script>
</body>
</html>
