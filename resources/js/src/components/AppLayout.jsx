import { CsrfField, ServerErrors } from './Forms.jsx';
import Brand from './Brand.jsx';

const roleNames = {
    UTILIZADOR: 'Utilizador',
    GESTOR_ESPACOS: 'Gestor de Espaços',
    ADMINISTRADOR: 'Administrador',
};

function NavigationLink({ href, children }) {
    const active = window.location.pathname === href;

    return <a className={active ? 'ativo' : ''} href={href}>{children}</a>;
}

export default function AppLayout({ user, state, title, children }) {
    const managesSpaces = ['GESTOR_ESPACOS', 'ADMINISTRADOR'].includes(user?.perfil);
    const isAdmin = user?.perfil === 'ADMINISTRADOR';
    const isCustomer = user?.perfil === 'UTILIZADOR';
    const content = (
        <>
            {state.flash.success && <p className="sucesso">{state.flash.success}</p>}
            <ServerErrors errors={state.errors} />
            {children}
        </>
    );

    if (isCustomer) {
        return (
            <div className="cliente-shell">
                <header className="cliente-nav">
                    <a className="cliente-marca" href="/painel" aria-label="Gestor de Reservas de Espaços — início">
                        <Brand inverse compact />
                    </a>
                    <nav aria-label="Navegação principal">
                        <NavigationLink href="/utilizador/reservar">Espaços</NavigationLink>
                        <NavigationLink href="/painel">As minhas reservas</NavigationLink>
                    </nav>
                    <div className="cliente-conta">
                        <span className="cliente-nome">{user?.nome}</span>
                        <form method="POST" action="/logout">
                            <CsrfField token={state.csrfToken} />
                            <button className="cliente-sair" type="submit">Sair</button>
                        </form>
                    </div>
                </header>
                <main className="cliente-main">
                    <h1 className="visualmente-oculto">{title}</h1>
                    {content}
                </main>
                <footer className="cliente-rodape">© {new Date().getFullYear()} ISTEC · Gestor de Reservas</footer>
            </div>
        );
    }

    return (
        <div className="painel">
            <aside className="lateral">
                <div className="lateral-logo">
                    <Brand inverse compact />
                </div>
                <nav>
                    <NavigationLink href="/painel">Início</NavigationLink>
                    <NavigationLink href="/utilizador/reservar">Reservar espaço</NavigationLink>
                    <span className="breve">As minhas reservas</span>

                    {managesSpaces && (
                        <>
                            <span className="grupo">Gestor de Espaços</span>
                            <NavigationLink href="/gestor">Pedidos</NavigationLink>
                            <NavigationLink href="/gestor/ocupacao">Ocupação global</NavigationLink>
                            <NavigationLink href="/gestor/indisponibilidades">Indisponibilidades</NavigationLink>
                            <NavigationLink href="/gestor/salas">Salas</NavigationLink>
                            <span className="breve">Regras de utilização</span>
                        </>
                    )}

                    {isAdmin && (
                        <>
                            <span className="grupo">Administração</span>
                            <NavigationLink href="/admin">Painel</NavigationLink>
                            <span className="breve">Espaços e recursos</span>
                            <span className="breve">Relatórios</span>
                        </>
                    )}
                </nav>
                <div className="lateral-fim">
                    {user?.nome}
                    <form method="POST" action="/logout">
                        <CsrfField token={state.csrfToken} />
                        <button className="btn" type="submit">Sair</button>
                    </form>
                </div>
            </aside>
            <div className="corpo">
                <header className="topo">
                    <h1>{title}</h1>
                    <span className="etiqueta">{roleNames[user?.perfil] ?? user?.perfil}</span>
                </header>
                <main>
                    {content}
                </main>
                <footer className="rodape">
                    <span>© {new Date().getFullYear()}</span>
                </footer>
            </div>
        </div>
    );
}
