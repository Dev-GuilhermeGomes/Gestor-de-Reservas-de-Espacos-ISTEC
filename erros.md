# Relatório QA — Gestor de Reservas ISTEC

Data: 2026-11-25 | Branch: `laraveltoreact` | Commit: `ad8cb56`

## Resumo

| Severidade | Número |
|---|---:|
| Crítica | 0 |
| Alta | 6 |
| Média | 2 |
| Baixa | 1 |

Prioridades: criar as migrações do esquema principal; implementar a submissão e aprovação de reservas; garantir que cada decisão e alteração de indisponibilidade respeita auditoria, notificações e preservação do histórico.

## Problemas encontrados

### ERR-01 | Alta | As migrações não criam o esquema principal
- Estado: confirmado.
- Ficheiros: [migração 2FA](database/migrations/2026_11_04_164230_add_two_factor_fields_to_utilizadores_table.php#L14), [teste de migrações](tests/Feature/ApplicationMigrationsTest.php#L12).
- Descrição: não há migração que crie `utilizadores`; as migrações de aplicação encontradas só alteram essa tabela. Um `migrate:fresh` falha ao tentar adicionar `two_factor_secret`.
- Reprodução: `ApplicationMigrationsTest::test_fresh_migrations_create_the_application_schema`.
- Resultado obtido: `no such table: utilizadores`.
- Consequência: uma instalação limpa não consegue criar a base de dados da aplicação.

### ERR-02 | Média | Rollback das migrações 2FA remove as colunas duas vezes
- Estado: confirmado.
- Ficheiros: [migração 2FA original](database/migrations/2026_11_04_164230_add_two_factor_fields_to_utilizadores_table.php#L24), [migração 2FA duplicada](database/migrations/2026_11_25_144647_add_two_factor_columns_to_utilizadores_table.php#L29), [teste](tests/Feature/ApplicationMigrationsTest.php#L21).
- Descrição: ambas as migrações removem as mesmas colunas no método `down()`. O rollback da segunda deixa a primeira a tentar removê-las de novo.
- Reprodução: `ApplicationMigrationsTest::test_rolling_back_both_two_factor_migrations_succeeds`.
- Resultado obtido: erro de coluna inexistente (`two_factor_secret`).

### ERR-03 | Alta | A página React de reservas não permite submeter reservas
- Estado: confirmado.
- Ficheiros: [rota](routes/web.php#L24), [página React](resources/js/src/pages/DashboardPages.jsx#L20), [teste](tests/Feature/ReservationSubmissionTest.php#L16).
- Descrição: a rota serve apenas o ecrã React; a página mostra texto placeholder e não existe rota POST para criar uma reserva.
- Reprodução: enviar `POST /utilizador/reservar` autenticado, ou executar `ReservationSubmissionTest::test_authenticated_user_gets_validation_errors_when_submission_fields_are_missing`.
- Resultado obtido: HTTP 405; não há erros de validação nem criação de reserva.

### ERR-04 | Alta | Um pedido pendente não pode ser aprovado
- Estado: confirmado.
- Ficheiros: [controller](app/Http/Controllers/GestorController.php#L108), [interface do gestor](resources/js/src/pages/ManagerPages.jsx#L106), [teste](tests/Feature/GestorControllerAuditTest.php#L123).
- Descrição: a atualização só aceita pedidos cujo estado atual seja `RECUSADA`; os pedidos pendentes não têm ação disponível na lista, e não há endpoint próprio para aprová-los.
- Reprodução: `GestorControllerAuditTest::test_pending_reservation_can_be_approved`.
- Resultado obtido: HTTP 404; o registo permanece `PENDENTE`.

### ERR-05 | Alta | São aceites transições de estado inválidas
- Estado: confirmado.
- Ficheiros: [recusa sem validação do estado atual](app/Http/Controllers/GestorController.php#L505), [aprovação de pedido recusado](app/Http/Controllers/GestorController.php#L112), [testes](tests/Feature/GestorControllerAuditTest.php#L87).
- Descrição: o endpoint de recusa aceita um pedido já recusado. Além disso, a atualização permite alterar um pedido recusado para `APROVADA`, apesar da regra definida para impedir essa transição.
- Reprodução: executar `test_refusal_rejects_a_reservation_that_is_not_approved` e `test_refused_reservation_cannot_be_approved_again`.
- Resultado obtido: a recusa repetida redireciona (302); no segundo caso, o estado passa a `APROVADA`.

### ERR-06 | Alta | Decisões não geram notificação/auditoria nem são transacionais
- Estado: confirmado.
- Ficheiro: [GestorController.php](app/Http/Controllers/GestorController.php#L505).
- Descrição: o método de recusa apenas atualiza a reserva. Não grava auditoria, não envia notificação e não envolve a decisão numa transação. A atualização de pedidos também não apresenta estes efeitos.
- Reprodução: [test_decision_sends_a_notification_to_the_requester](tests/Feature/GestorControllerAuditTest.php#L159), [test_decision_creates_an_audit_record](tests/Feature/GestorControllerAuditTest.php#L177) e [test_decision_rolls_back_when_audit_persistence_fails](tests/Feature/GestorControllerAuditTest.php#L200).
- Resultado obtido: nenhuma notificação; zero registos em `auditoria`; a reserva muda para `RECUSADA` mesmo quando o teste configura uma falha de escrita na auditoria.

### ERR-07 | Alta | Indisponibilidades são apagadas em vez de desativadas
- Estado: confirmado.
- Ficheiro: [GestorController.php](app/Http/Controllers/GestorController.php#L334).
- Descrição: tanto a ação de apagar como a de marcar o espaço disponível executam `DELETE`, eliminando o histórico.
- Reprodução: [test_deleting_unavailability_preserves_its_history](tests/Feature/GestorControllerAuditTest.php#L216) e [test_marking_unavailability_available_preserves_its_history](tests/Feature/GestorControllerAuditTest.php#L234).
- Resultado obtido: a tabela `indisponibilidades` fica sem o registo.

### ERR-08 | Média | É possível aprovar uma reserva com datas no passado
- Estado: confirmado.
- Ficheiros: [validação da atualização](app/Http/Controllers/GestorController.php#L116), [teste](tests/Feature/GestorControllerAuditTest.php#L143).
- Descrição: as datas são validadas como datas e o fim tem de ser posterior ao início, mas não existe regra que exija início no presente ou futuro.
- Reprodução: `GestorControllerAuditTest::test_approving_a_past_reservation_is_rejected`.
- Resultado obtido: não são devolvidos erros de validação; o pedido de aprovação é aceite.

### ERR-09 | Baixa | A factory de utilizadores não corresponde ao modelo da aplicação
- Estado: confirmado.
- Ficheiros: [User.php](app/Models/User.php#L7), [UserFactory.php](database/factories/UserFactory.php#L28), [testes](tests/Feature/UserFactoryTest.php#L11).
- Descrição: o modelo não usa `HasFactory` e a factory define `name`, `email_verified_at` e `remember_token`, enquanto o modelo usa `nome` e a tabela `utilizadores`.
- Reprodução: `UserFactoryTest::test_user_model_exposes_its_factory` e `test_factory_uses_the_custom_user_name_attribute`.
- Resultado obtido: o modelo não expõe `factory()` e a factory não preenche `nome`.

## Testes atualizados para React

- Corrigido o teste de `/`: o comportamento atual é redirecionar um visitante para `/login`, não devolver HTTP 200.
- Adicionado [ReactPageRenderingTest.php](tests/Feature/ReactPageRenderingTest.php): valida o shell React no login, na reserva e na administração, bem como o bloqueio de utilizadores não-admin.
- Adicionado teste de renderização React à página do gestor em [GestorControllerAuditTest.php](tests/Feature/GestorControllerAuditTest.php#L189).
- Mantidos testes reprodutores para os ERR-01–ERR-09; por isso, a suite continuará vermelha até os problemas da aplicação e da factory serem corrigidos.

## Verificado e correto

- `npm.cmd run build`: passou. Apenas avisou que o pacote opcional `fontaine` não está instalado.
- Browser `/login`: React renderizado, campo CSRF presente e sem erros JavaScript.
- Testes do shell React e permissões da rota de administração: passaram.
- Acesso anónimo a `/utilizador/reservar`: redireciona para login.
- Os testes usaram SQLite `:memory:`; não foi executada migração na base de dados principal nem alterado o `.env`.

## Não verificado

- Não existe `.env.testing` nem ficheiro `.sql` de schema no repositório. Não foi possível validar triggers, restrições ou concorrência em PostgreSQL.
- Imutabilidade da auditoria, isolamento completo entre gestores, reutilização de código 2FA e cenários de concorrência não foram confirmados nesta execução.
- O login real e os ecrãs protegidos não foram testados com dados PostgreSQL.

## Saída resumida dos comandos

```text
php artisan test --compact
16 testes: 2 passaram, 3 falharam e 11 deram erro.
Motivo dos erros de base de dados: o PHP CLI não carregava pdo_sqlite.
```

```text
php -d extension=php_sqlite3.dll -d extension=php_pdo_sqlite.dll vendor/bin/phpunit --no-progress --no-coverage
22 testes: 8 passaram, 12 falharam e 2 deram erro.
Os erros de migração e as falhas funcionais estão detalhados acima.
```

```text
POST /utilizador/reservar
HTTP 405 Method Not Allowed
```

```text
npm.cmd run build
✓ built in 3.02s
Aviso: pacote opcional "fontaine" ausente.
```
