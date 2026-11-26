import { useState } from 'react';
import { CsrfField, FieldError } from '../components/Forms.jsx';

const spaceName = (space) => [space?.edificio, space?.nome].filter(Boolean).join(' - ');
const oldValue = (state, name, fallback = '') => state.old?.[name] ?? fallback ?? '';

function formatDate(value) {
    return new Intl.DateTimeFormat('pt-PT', {
        dateStyle: 'short',
        timeZone: 'Europe/Lisbon',
    }).format(new Date(value));
}

function formatTime(value) {
    return new Intl.DateTimeFormat('pt-PT', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'Europe/Lisbon',
    }).format(new Date(value));
}

function dateTimeLocal(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value);
    const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);

    return localDate.toISOString().slice(0, 16);
}

function ReservationDetails({ reservation }) {
    return (
        <dl>
            <div>
                <dt>Espaço</dt>
                <dd>{spaceName(reservation.espaco) || 'Espaço desconhecido'}</dd>
            </div>
            <div>
                <dt>Requerente</dt>
                <dd>{reservation.utilizador?.nome ?? 'Utilizador desconhecido'}</dd>
            </div>
            <div>
                <dt>Data e horário</dt>
                <dd>{formatDate(reservation.inicio)} {formatTime(reservation.inicio)} - {formatTime(reservation.fim)}</dd>
            </div>
            <div>
                <dt>Finalidade</dt>
                <dd>{reservation.motivo}</dd>
            </div>
        </dl>
    );
}

export function ManagerRequestsPage({ pedidos = [], estadoAtual, aprovadasSemana, indisponibilidades }) {
    const [searchOpen, setSearchOpen] = useState(false);
    const [search, setSearch] = useState('');
    const filtered = pedidos.filter((pedido) => {
        const searchable = [
            spaceName(pedido.espaco),
            pedido.utilizador?.nome,
            pedido.motivo,
            pedido.estado,
        ].join(' ').toLocaleLowerCase();

        return searchable.includes(search.trim().toLocaleLowerCase());
    });

    return (
        <>
            <div className="resumo">
                <div className="cartao"><strong>{aprovadasSemana}</strong>Reservas aprovadas esta semana</div>
                <div className="cartao"><strong>{indisponibilidades}</strong>Indisponibilidades ativas</div>
            </div>
            <div className="cartao">
                <div className="separadores" role="tablist" aria-label="Estado dos pedidos">
                    <a className={estadoAtual === 'APROVADA' ? 'ativo' : ''} href="/gestor?estado=APROVADA">Aprovadas</a>
                    <a className={estadoAtual === 'RECUSADA' ? 'ativo' : ''} href="/gestor?estado=RECUSADA">Recusadas</a>
                    <a className={estadoAtual === 'TODAS' ? 'ativo' : ''} href="/gestor?estado=TODAS">Todas</a>
                    <button className="botao-pesquisa" type="button" aria-label="Abrir pesquisa" aria-expanded={searchOpen} onClick={() => setSearchOpen(!searchOpen)}>
                        <svg aria-hidden="true" viewBox="0 0 24 24" focusable="false"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 5 5" /></svg>
                    </button>
                </div>
                {searchOpen && (
                    <div className="caixa-pesquisa">
                        <label htmlFor="pesquisa-pedidos">Pesquisar pedidos</label>
                        <input id="pesquisa-pedidos" type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Espaço, requerente ou finalidade..." autoComplete="off" />
                    </div>
                )}
                <div className="tabela">
                    <table>
                        <thead>
                            <tr><th>Espaço</th><th>Requerente</th><th>Data e hora</th><th>Participantes</th><th>Motivo</th><th>Estado</th><th>Ações</th></tr>
                        </thead>
                        <tbody>
                            {filtered.map((pedido) => (
                                <tr key={pedido.id}>
                                    <td>{spaceName(pedido.espaco) || 'Espaço desconhecido'}</td>
                                    <td>{pedido.utilizador?.nome ?? 'Utilizador desconhecido'}</td>
                                    <td>{formatDate(pedido.inicio)} {formatTime(pedido.inicio)} - {formatTime(pedido.fim)}</td>
                                    <td>{pedido.participantes}</td>
                                    <td>{pedido.motivo}</td>
                                    <td><span className={`estado estado-${pedido.estado.toLowerCase()}`}>{pedido.estado[0] + pedido.estado.slice(1).toLowerCase()}</span></td>
                                    <td>
                                        {pedido.estado === 'APROVADA' ? (
                                            <a className="botao botao-secundario" href={`/gestor/pedidos/${pedido.id}/recusar`}>Recusar</a>
                                        ) : pedido.estado === 'RECUSADA' ? (
                                            <div className="acoes">
                                                <a className="botao botao-secundario" href={`/gestor/pedidos/${pedido.id}/motivo`}>Ver motivo</a>
                                                <a className="botao botao-principal" href={`/gestor/pedidos/${pedido.id}/editar`}>Editar</a>
                                            </div>
                                        ) : <span className="acao-concluida">Sem ações disponíveis</span>}
                                    </td>
                                </tr>
                            ))}
                            {filtered.length === 0 && (
                                <tr><td colSpan="7" className="vazio">
                                    {search ? 'Não foram encontrados pedidos para esta pesquisa.' : 'Ainda não há pedidos.'}
                                </td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </>
    );
}

export function RefusalPage({ pedido, state }) {
    return (
        <section className="cartao detalhes-reserva">
            <h2>Recusar reserva</h2>
            <p>Indica o motivo pelo qual esta reserva não pode ser mantida.</p>
            <ReservationDetails reservation={pedido} />
            <form method="POST" action={`/gestor/pedidos/${pedido.id}/recusar`}>
                <CsrfField token={state.csrfToken} />
                <label htmlFor="justificacao_decisao">Motivo da recusa</label>
                <textarea id="justificacao_decisao" name="justificacao_decisao" defaultValue={oldValue(state, 'justificacao_decisao')} required minLength="3" />
                <FieldError errors={state.errors} name="justificacao_decisao" />
                <div className="acoes-formulario">
                    <a className="botao botao-secundario" href="/gestor">Cancelar</a>
                    <button className="botao botao-principal" type="submit">Confirmar recusa</button>
                </div>
            </form>
        </section>
    );
}

export function RefusalReasonPage({ pedido }) {
    return (
        <section className="cartao detalhes-reserva">
            <h2>Motivo da recusa</h2>
            <ReservationDetails reservation={pedido} />
            <div className="motivo-destaque">{pedido.justificacao_decisao ?? 'Sem motivo indicado.'}</div>
            <div className="acoes-formulario">
                <a className="botao botao-secundario" href="/gestor?estado=RECUSADA">Voltar às recusadas</a>
            </div>
        </section>
    );
}

export function EditReservationPage({ pedido, espacos, state }) {
    return (
        <section className="cartao detalhes-reserva">
            <h2>Editar reserva recusada</h2>
            <p>Podes manter a recusa, aprovar a reserva ou escolher outra sala e horário.</p>
            <form method="POST" action={`/gestor/pedidos/${pedido.id}`}>
                <CsrfField token={state.csrfToken} />
                <input type="hidden" name="_method" value="PUT" />
                <label htmlFor="estado">Decisão</label>
                <select id="estado" name="estado" defaultValue={oldValue(state, 'estado', pedido.estado)} required>
                    <option value="APROVADA">Aprovar reserva</option>
                    <option value="RECUSADA">Manter recusada</option>
                </select>
                <FieldError errors={state.errors} name="estado" />
                <label htmlFor="espaco_id">Sala</label>
                <select id="espaco_id" name="espaco_id" defaultValue={oldValue(state, 'espaco_id', pedido.espaco_id)} required>
                    {espacos.map((space) => <option key={space.id} value={space.id}>{spaceName(space)} (capacidade: {space.capacidade})</option>)}
                </select>
                <FieldError errors={state.errors} name="espaco_id" />
                <label htmlFor="inicio">Início</label>
                <input id="inicio" name="inicio" type="datetime-local" defaultValue={oldValue(state, 'inicio', dateTimeLocal(pedido.inicio))} required />
                <FieldError errors={state.errors} name="inicio" />
                <label htmlFor="fim">Fim</label>
                <input id="fim" name="fim" type="datetime-local" defaultValue={oldValue(state, 'fim', dateTimeLocal(pedido.fim))} required />
                <FieldError errors={state.errors} name="fim" />
                <label htmlFor="justificacao_decisao">Motivo / observação da decisão</label>
                <textarea id="justificacao_decisao" name="justificacao_decisao" defaultValue={oldValue(state, 'justificacao_decisao', pedido.justificacao_decisao)} required minLength="3" />
                <FieldError errors={state.errors} name="justificacao_decisao" />
                <div className="acoes-formulario">
                    <a className="botao botao-secundario" href="/gestor?estado=RECUSADA">Cancelar</a>
                    <button className="botao botao-principal" type="submit">Guardar alteração</button>
                </div>
            </form>
        </section>
    );
}

export function OccupancyPage({ espacos, tipos, ocupacao, periodo, totalReservas, mediaOcupacao }) {
    const search = new URLSearchParams(window.location.search);

    return (
        <>
            <form className="filtros" method="GET">
                <label htmlFor="filtro-espaco">Espaço
                    <select id="filtro-espaco" name="espaco" defaultValue={search.get('espaco') ?? ''}>
                        <option value="">Todas</option>
                        {espacos.map((space) => <option key={space.id} value={space.id}>{spaceName(space)}</option>)}
                    </select>
                </label>
                <label htmlFor="filtro-tipo">Tipo de espaço
                    <select id="filtro-tipo" name="tipo" defaultValue={search.get('tipo') ?? ''}>
                        <option value="">Todos</option>
                        {tipos.map((type) => <option key={type} value={type}>{type}</option>)}
                    </select>
                </label>
                <label htmlFor="filtro-periodo">Período
                    <select id="filtro-periodo" name="periodo" defaultValue={periodo}>
                        <option value="semana">Semana atual</option>
                        <option value="mes">Mês atual</option>
                    </select>
                </label>
                <button className="botao botao-principal" type="submit">Aplicar</button>
            </form>
            <div className="resumo resumo-ocupacao">
                <div className="cartao"><strong>{mediaOcupacao}%</strong>Taxa média de ocupação</div>
                <div className="cartao"><strong>{totalReservas}</strong>Reservas aprovadas</div>
                <div className="cartao"><strong>0</strong>Conflitos evitados</div>
            </div>
            <section className="cartao">
                <h2 className="titulo-cartao">Ocupação por espaço</h2>
                <div className="barras-ocupacao">
                    {ocupacao.length ? ocupacao.map((row) => (
                        <div className="barra-linha" key={row.espaco.id}>
                            <span>{row.espaco.nome}</span>
                            <div className="barra-fundo"><div className="barra-valor" style={{ width: `${row.percentagem}%` }} /></div>
                            <strong>{row.percentagem}%</strong>
                        </div>
                    )) : <p className="vazio">Não existem espaços disponíveis.</p>}
                </div>
                <p className="nota">Taxa de ocupação calculada com base nas reservas aprovadas para o período selecionado.</p>
            </section>
        </>
    );
}

export function UnavailabilitiesPage({ indisponibilidades, estadoAtual, contagens, state }) {
    const [search, setSearch] = useState('');
    const filtered = indisponibilidades.filter((item) => {
        const content = `${item.edificio} ${item.nome} ${item.motivo}`.toLocaleLowerCase();

        return content.includes(search.trim().toLocaleLowerCase()) && (estadoAtual === 'TODOS' || item.estado === estadoAtual);
    });

    return (
        <>
            <div className="resumo resumo-indisponibilidades">
                <div className="cartao"><strong>{contagens.DISPONIVEL}</strong>Espaços disponíveis</div>
                <div className="cartao"><strong>{contagens.MANUTENCAO}</strong>Espaços em manutenção</div>
                <div className="cartao"><strong>{contagens.INDISPONIVEL}</strong>Espaços indisponíveis</div>
                <div className="cartao"><strong>{contagens.CANCELADO}</strong>Espaços cancelados</div>
            </div>
            <section className="cartao">
                <div className="cabecalho-cartao">
                    <h2 className="titulo-cartao">Gestão de indisponibilidades</h2>
                    <a className="botao botao-principal" href="/gestor/indisponibilidades/criar">Nova indisponibilidade</a>
                </div>
                <div className="separadores" role="tablist" aria-label="Estado dos espaços">
                    {[
                        ['CANCELADO', 'Cancelados'],
                        ['MANUTENCAO', 'Manutenções'],
                        ['INDISPONIVEL', 'Indisponíveis'],
                        ['DISPONIVEL', 'Disponíveis'],
                        ['TODOS', 'Todos'],
                    ].map(([value, name]) => (
                        <a key={value} className={estadoAtual === value ? 'ativo' : ''} href={`/gestor/indisponibilidades?estado=${value}`}>{name}</a>
                    ))}
                </div>
                <div className="caixa-pesquisa">
                    <label htmlFor="pesquisa-indisponibilidades">Pesquisar espaços</label>
                    <input id="pesquisa-indisponibilidades" type="search" value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Espaço ou motivo..." autoComplete="off" />
                </div>
                <div className="tabela">
                    <table>
                        <thead><tr><th>Espaço</th><th>Data</th><th>Horário</th><th>Motivo</th><th>Estado</th><th>Ações</th></tr></thead>
                        <tbody>
                            {filtered.map((item, index) => (
                                <tr key={item.id ?? `available-${item.espaco_id}-${index}`}>
                                    <td>{item.edificio} - {item.nome}</td>
                                    <td>{formatDate(item.inicio)}</td>
                                    <td>{formatTime(item.inicio)} - {formatTime(item.fim)}</td>
                                    <td>{item.motivo}</td>
                                    <td><span className={`estado estado-${item.estado.toLowerCase()}`}>{item.estado[0] + item.estado.slice(1).toLowerCase()}</span></td>
                                    <td>
                                        {item.estado !== 'DISPONIVEL' ? (
                                            <div className="acoes">
                                                <a className="botao botao-principal" href={`/gestor/indisponibilidades/${item.id}/editar`}>Editar</a>
                                                <form method="POST" action={`/gestor/indisponibilidades/${item.id}`} onSubmit={(event) => {
                                                    if (!window.confirm('Apagar esta indisponibilidade?')) event.preventDefault();
                                                }}>
                                                    <CsrfField token={state.csrfToken} />
                                                    <input type="hidden" name="_method" value="DELETE" />
                                                    <button className="botao botao-secundario" type="submit">Apagar</button>
                                                </form>
                                            </div>
                                        ) : (
                                            <a className="botao botao-secundario" href={`/gestor/indisponibilidades/criar?espaco_id=${item.espaco_id}`}>Bloquear</a>
                                        )}
                                    </td>
                                </tr>
                            ))}
                            {filtered.length === 0 && <tr><td colSpan="6" className="vazio">Não foram encontrados resultados.</td></tr>}
                        </tbody>
                    </table>
                </div>
            </section>
        </>
    );
}

export function CreateUnavailabilityPage({ espacos, estados, state }) {
    return (
        <section className="cartao detalhes-reserva">
            <h2>Marcar sala como indisponível</h2>
            <p>Escolhe uma sala, o período e o estado que deve ser aplicado.</p>
            <form method="POST" action="/gestor/indisponibilidades">
                <CsrfField token={state.csrfToken} />
                <label htmlFor="espaco_id">Sala</label>
                <select id="espaco_id" name="espaco_id" defaultValue={oldValue(state, 'espaco_id', new URLSearchParams(window.location.search).get('espaco_id'))} required>
                    <option value="">Seleciona uma sala</option>
                    {espacos.map((space) => <option key={space.id} value={space.id}>{spaceName(space)}</option>)}
                </select>
                <FieldError errors={state.errors} name="espaco_id" />
                <label htmlFor="inicio">Início</label>
                <input id="inicio" name="inicio" type="datetime-local" defaultValue={oldValue(state, 'inicio')} required />
                <FieldError errors={state.errors} name="inicio" />
                <label htmlFor="fim">Fim</label>
                <input id="fim" name="fim" type="datetime-local" defaultValue={oldValue(state, 'fim')} required />
                <FieldError errors={state.errors} name="fim" />
                <label htmlFor="estado">Estado</label>
                <select id="estado" name="estado" defaultValue={oldValue(state, 'estado')} required>
                    <option value="">Seleciona um estado</option>
                    {Object.entries(estados).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                </select>
                <FieldError errors={state.errors} name="estado" />
                <div className="acoes-formulario">
                    <a className="botao botao-secundario" href="/gestor/indisponibilidades">Cancelar</a>
                    <button className="botao botao-principal" type="submit">Guardar indisponibilidade</button>
                </div>
            </form>
        </section>
    );
}

export function EditUnavailabilityPage({ indisponibilidade, espacos, estados, estadoAtual, state }) {
    return (
        <section className="cartao detalhes-reserva">
            <h2>Editar indisponibilidade</h2>
            <form method="POST" action={`/gestor/indisponibilidades/${indisponibilidade.id}`}>
                <CsrfField token={state.csrfToken} />
                <input type="hidden" name="_method" value="PUT" />
                <label htmlFor="espaco_id">Espaço</label>
                <select id="espaco_id" name="espaco_id" defaultValue={oldValue(state, 'espaco_id', indisponibilidade.espaco_id)} required>
                    {espacos.map((space) => <option key={space.id} value={space.id}>{spaceName(space)}</option>)}
                </select>
                <FieldError errors={state.errors} name="espaco_id" />
                <label htmlFor="inicio">Início</label>
                <input id="inicio" name="inicio" type="datetime-local" defaultValue={oldValue(state, 'inicio', dateTimeLocal(indisponibilidade.inicio))} required />
                <FieldError errors={state.errors} name="inicio" />
                <label htmlFor="fim">Fim</label>
                <input id="fim" name="fim" type="datetime-local" defaultValue={oldValue(state, 'fim', dateTimeLocal(indisponibilidade.fim))} required />
                <FieldError errors={state.errors} name="fim" />
                <label htmlFor="estado">Estado</label>
                <select id="estado" name="estado" defaultValue={oldValue(state, 'estado', estadoAtual)} required>
                    {Object.entries(estados).map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                </select>
                <FieldError errors={state.errors} name="estado" />
                <div className="acoes-formulario">
                    <a className="botao botao-secundario" href="/gestor/indisponibilidades">Cancelar</a>
                    <button className="botao botao-principal" type="submit">Guardar alterações</button>
                </div>
            </form>
        </section>
    );
}

export function RoomsPage({ espacos }) {
    return (
        <section className="cartao lista-salas">
            <div className="cabecalho-cartao">
                <h2 className="titulo-cartao">Salas disponíveis</h2>
                <a className="botao botao-principal" href="/gestor/salas/criar">Nova sala</a>
            </div>
            <div className="tabela">
                <table>
                    <thead><tr><th>Espaço</th><th>Tipo</th><th>Capacidade</th><th>Piso</th><th>Equipamentos</th><th>Ações</th></tr></thead>
                    <tbody>
                        {espacos.map((space) => (
                            <tr key={space.id}>
                                <td>{spaceName(space)}</td>
                                <td>{space.tipo}</td>
                                <td>{space.capacidade}</td>
                                <td>{space.piso ?? '-'}</td>
                                <td>{space.equipamentos?.map((equipment) => equipment.nome).join(', ') || '-'}</td>
                                <td><a className="botao botao-secundario" href={`/gestor/salas/${space.id}/editar`}>Editar</a></td>
                            </tr>
                        ))}
                        {espacos.length === 0 && <tr><td className="vazio" colSpan="6">Ainda não existem salas disponíveis.</td></tr>}
                    </tbody>
                </table>
            </div>
        </section>
    );
}

function RoomFields({ state, space = {} }) {
    return (
        <>
            <div className="form-grelha">
                <div>
                    <label htmlFor="edificio">Edifício</label>
                    <input id="edificio" name="edificio" defaultValue={oldValue(state, 'edificio', space.edificio ?? 'ISTEC')} required maxLength="100" />
                    <FieldError errors={state.errors} name="edificio" />
                </div>
                <div>
                    <label htmlFor="nome">Nome da sala</label>
                    <input id="nome" name="nome" defaultValue={oldValue(state, 'nome', space.nome)} required maxLength="100" />
                    <FieldError errors={state.errors} name="nome" />
                </div>
                <div>
                    <label htmlFor="tipo">Tipo de espaço</label>
                    <select id="tipo" name="tipo" defaultValue={oldValue(state, 'tipo', space.tipo ?? '')} required>
                        <option value="">Seleciona um tipo</option>
                        <option value="SALA">Sala</option>
                        <option value="LABORATORIO">Laboratório</option>
                        <option value="AUDITORIO">Auditório</option>
                        <option value="ESPACO_ESTUDO">Espaço de estudo</option>
                    </select>
                    <FieldError errors={state.errors} name="tipo" />
                </div>
                <div>
                    <label htmlFor="capacidade">Capacidade</label>
                    <input id="capacidade" name="capacidade" type="number" min="1" defaultValue={oldValue(state, 'capacidade', space.capacidade ?? 1)} required />
                    <FieldError errors={state.errors} name="capacidade" />
                </div>
                <div>
                    <label htmlFor="piso">Piso</label>
                    <input id="piso" name="piso" type="number" defaultValue={oldValue(state, 'piso', space.piso ?? 0)} />
                    <FieldError errors={state.errors} name="piso" />
                </div>
            </div>
            <label htmlFor="descricao">Descrição</label>
            <textarea id="descricao" name="descricao" defaultValue={oldValue(state, 'descricao', space.descricao)} />
            <FieldError errors={state.errors} name="descricao" />
        </>
    );
}

export function CreateRoomPage({ state }) {
    return (
        <section className="cartao detalhes-reserva">
            <h2>Criar sala</h2>
            <p>Depois de criar a sala, podes gerir os equipamentos na opção <strong>Editar</strong>.</p>
            <form method="POST" action="/gestor/salas">
                <CsrfField token={state.csrfToken} />
                <RoomFields state={state} />
                <div className="acoes-formulario">
                    <a className="botao botao-secundario" href="/gestor/salas">Cancelar</a>
                    <button className="botao botao-principal" type="submit">Criar sala</button>
                </div>
            </form>
        </section>
    );
}

export function EditRoomPage({ espaco, state }) {
    return (
        <>
            <section className="cartao detalhes-reserva">
                <h2>Editar sala</h2>
                <form method="POST" action={`/gestor/salas/${espaco.id}`}>
                    <CsrfField token={state.csrfToken} />
                    <input type="hidden" name="_method" value="PUT" />
                    <RoomFields state={state} space={espaco} />
                    <div className="acoes-formulario">
                        <a className="botao botao-secundario" href="/gestor/salas">Cancelar</a>
                        <button className="botao botao-principal" type="submit">Guardar alterações</button>
                    </div>
                </form>
            </section>
            <section className="cartao lista-salas">
                <h2 className="titulo-cartao">Equipamentos da sala</h2>
                <form method="POST" action={`/gestor/salas/${espaco.id}/equipamentos`}>
                    <CsrfField token={state.csrfToken} />
                    <div className="form-grelha">
                        <div>
                            <label htmlFor="equipamento_nome">Nome do equipamento</label>
                            <input id="equipamento_nome" name="nome" required maxLength="100" placeholder="Projetor" />
                            <FieldError errors={state.errors} name="nome" />
                        </div>
                        <div>
                            <label htmlFor="equipamento_descricao">Descrição</label>
                            <input id="equipamento_descricao" name="descricao" maxLength="255" placeholder="Projetor de vídeo" />
                            <FieldError errors={state.errors} name="descricao" />
                        </div>
                    </div>
                    <label className="opcao-checkbox">
                        <input type="checkbox" name="reservavel" value="1" />
                        Pode ser reservado separadamente
                    </label>
                    <div className="acoes-formulario">
                        <button className="botao botao-principal" type="submit">Adicionar equipamento</button>
                    </div>
                </form>
                <div className="tabela">
                    <table>
                        <thead><tr><th>Equipamento</th><th>Descrição</th><th>Reservável</th></tr></thead>
                        <tbody>
                            {espaco.equipamentos?.map((equipment) => (
                                <tr key={equipment.id}>
                                    <td>{equipment.nome}</td>
                                    <td>{equipment.descricao || '-'}</td>
                                    <td>{equipment.reservavel ? 'Sim' : 'Não'}</td>
                                </tr>
                            ))}
                            {(!espaco.equipamentos || espaco.equipamentos.length === 0) && <tr><td className="vazio" colSpan="3">Ainda não existem equipamentos associados.</td></tr>}
                        </tbody>
                    </table>
                </div>
            </section>
        </>
    );
}
