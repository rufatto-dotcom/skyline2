# Skyline2

Da mesma forma que a batata se adapta, este “framework” também.
Ele não se importa se você tem 50 tabelas ou 5.000 registros. Ele só quer o seu banco.

## Compatibilidade

- PHP ≥ 7.2 (testado também em versões mais antigas)
- MySQL / MariaDB
- Windows (ambiente principal de desenvolvimento e testes) ou Linux

O sistema não depende de extensões específicas do SO.
MacOS não foi alvo de testes.

Desempenho direto.
O que é inútil é descartado.

Skyline2 é previsível.

## Por que eu criei ele?
CRMs grandes acumulam camadas, convenções e acoplamentos difíceis de entender.
Skyline2 existe para evitar isso.

É flexível, manipulável e livre de frameworks externos.
Usei sintaxe o mais próxima possível do padrão, para que o sistema seja fácil de entender e até de portar para outras linguagens.

Menos camadas, menos surpresas.

## Como funciona?

O banco de dados é a única fonte de verdade.
O sistema não impõe estrutura. Ele expõe o banco como ele é.

Pontos centrais do sistema:

dao/DAO.php: acesso ao banco e operações principais.

metadata/metadata.php: tradução da estrutura do banco para a interface.

core/requestHandler.php: toda requisição passa por aqui.

O sistema é subir e acessar.
Se não estiver instalado, o instalador automático entra em ação e resolve o necessário.

### Decisão de projeto

- Os três arquivos centrais foram escritos de forma previsível e direta.
- A ideia é que qualquer desenvolvedor consiga entender o funcionamento do sistema sem depender de “magia” ou convenções escondidas.

## Para quem faz sentido?

- Empresas pequenas e startups.
- Projetos que querem CRUD rápido, previsível e extensível.
- Times pequenos que preferem customização direta a estruturas engessadas.
- Atualmente não há suporte nativo a relacionamentos N:N, embora a arquitetura já esteja preparada para esse upgrade.

## Third-party libraries

- This project includes the FPDF library.
- FPDF is distributed under its own license.
- See the license file inside core/services/export/pdf/fpdf/.