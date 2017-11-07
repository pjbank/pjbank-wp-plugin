# Plugin PJBank para WooCommerce
Plugin de Wordpress (WooCommerce) para emissão de boletos via PJBank. 

### Como funciona ###

Após a instalação e configuração do plugin, a opção de boleto será adicionada as opções de pagamento, na última etapa do checkout.
Após a confirmação do método de pagamento, será aberto um popup com o boleto completo, para o usuário poder imprimir, salvar em seu computador ou fazer seu pagamento na hora.

No painel administrativo (wp-admin), diversas informações serão salvas juntos ao pedido no WooCommerce, como o link do boleto, o Nosso Número do boleto e o JSON de respota da API do PJBank.

## Instalação ##

### Pré-requisito ###

Como este plugin foi desenvolvido com o intuito de trabalhar em conjunto com o WooCommerce, é necessário a utilização do mesmo, caso contrário, não será possível a utilização deste plugin.

### Upload no Painel Administrativo do WordPress ###

1. Baixe o repositório <a href="https://github.com/pjbank/pjbank-wp-plugin/archive/master.zip" target="_blank">pjbank-wp-plugin</a>
2. Acesse o Painel Administrativo do WordPress e navegue até o menu Plugins.
3. Escolha a opção para adicionar um novo plugin.
4. Ative o botão para fazer o upload manual do plugin.
5. Selecione o arquivo `pjbank-wp-plugin-master.zip` em seu computador
6. Clique em 'Instalar Agora'.
7. Ative o plugin `Boleto PJBank`.

### Upload via FTP ###

1. Baixe o repositório <a href="https://github.com/pjbank/pjbank-wp-plugin/archive/master.zip" target="_blank">pjbank-wp-plugin</a>
2. Extraia o arquivo em seu computador.
3. Faça o upload da pasta `pjbank-wp-plugin-master` no diretório `/wp-content/plugins/`.
2. Acesse o Painel Administrativo do WordPress e navegue até o menu Plugins.
7. Ative o plugin `Boleto PJBank`.

### Usando o Painel Administrativo do WordPress ###

Ainda não disponível

## Configuração ##

Após a instalação e ativação, é necessário fazer a configuração dele, seguindo o caminho `WooCommerce > Configurações > Checkout > PJBank` no Painel Administrativo, onde será necessário configurar as opções abaixo:

* Habilitar/Desabilitar - Este checkbox irá ativar ou desativar o plugin.
* Token - Token PJBank da empresa, necessário para o correto funcionamento do plugin.
* Título - O nome que o plugin irá exibir no final do checkout. Padrão: Boleto Bancário.
* Vencimento - Quantidade de dias até o vencimento do boleto. Padrão: 0
* Juros - Taxa de juros ao dia por atraso. Padrão: 0.0
* Multa - Taxa de multa por atraso. Padrão: 0.0
* Desconto - Valor do desconto por pontualidade, em Reais. Padrão: 0
* Logo - URL do logo da empresa. 