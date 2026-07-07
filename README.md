# Tema tikporn

Tema WordPress estilo TikTok para catálogo de vídeos de modelos.

## Como instalar

1. Copie a pasta `tikporn` para dentro de `wp-content/themes/` da sua instalação WordPress.
2. No painel do WordPress, vá em **Aparência → Temas** e ative o tema **tikporn**.
3. Ao ativar, o tema cria sozinho as páginas: **Entrar**, **Cadastro**, **Área da modelo** e **Buscar**, e define a página inicial como o feed.
4. Em **Configurações → Links permanentes**, clique em **Salvar** uma vez (garante que os links dos vídeos e perfis funcionem).

## Como usar

- **Feed (página inicial):** rolagem vertical de vídeos, um por tela.
- **Cadastro:** qualquer visitante cria conta em `/cadastro/`. Marcando "Quero me cadastrar como modelo", a conta recebe o papel de modelo.
- **Login:** em `/entrar/`.
- **Área da modelo:** em `/area-modelo/` (só para modelos). Envia vídeo (arquivo MP4 ou link), edita o perfil e exclui vídeos.
- **Perfil da modelo:** endereço público de autor, ex. `/author/nome-da-modelo/`.

## Estrutura de pastas

```
tikporn/
├── style.css              cabeçalho do tema
├── functions.php          carrega tudo
├── header.php / footer.php
├── front-page.php         o feed
├── index.php              reserva
├── author.php             perfil da modelo
├── single-video.php       página de um vídeo
├── page.php / 404.php
├── inc/                   lógica (tipos, papéis, login, envios, curtidas)
├── page-templates/        login, cadastro, área da modelo, buscar
├── template-parts/        card de vídeo do feed
└── assets/                css e js
```

## Observações importantes

- Por ser conteúdo adulto, antes de publicar adicione: confirmação de idade, termos de uso, política de privacidade e verificação das modelos. Isso é regra de negócio, não faz parte do tema.
- Para vídeos grandes, ajuste no servidor os limites de upload do PHP (`upload_max_filesize` e `post_max_size`).
- O tema não depende de nenhum plugin pago.
