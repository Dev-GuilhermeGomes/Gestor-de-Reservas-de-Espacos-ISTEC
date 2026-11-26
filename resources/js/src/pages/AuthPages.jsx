import { CsrfField, FieldError } from '../components/Forms.jsx';
import Brand from '../components/Brand.jsx';

function AuthShell({ heading, description, children }) {
    return (
        <div className="login">
            <section className="login-lado">
                <Brand inverse />
                <div className="login-copy">
                    <span className="login-eyebrow">ESPAÇOS REAIS. RESERVAS SIMPLES.</span>
                    <h2>{heading}</h2>
                    <p>{description}</p>
                </div>
                <span className="login-nota">ISTEC · GESTÃO DE ESPAÇOS</span>
            </section>
            <section className="login-form">
                <div className="login-caixa">
                    {children}
                </div>
            </section>
        </div>
    );
}

export function LoginPage({ state }) {
    return (
        <AuthShell heading="Gestor de Reservas de Espaços" description="Salas, laboratórios, auditórios e equipamentos do ISTEC num só sítio, sem conflitos de utilização.">
            <h1>Iniciar sessão</h1>
            <form method="POST" action="/login">
                <CsrfField token={state.csrfToken} />
                <label htmlFor="email">E-mail</label>
                <input id="email" type="email" name="email" defaultValue={state.old.email ?? ''} required autoFocus />
                <FieldError errors={state.errors} name="email" />
                <label htmlFor="password">Palavra-passe</label>
                <input id="password" type="password" name="password" required />
                <button className="btn" type="submit">Entrar</button>
            </form>
        </AuthShell>
    );
}

export function ChallengePage({ state }) {
    return (
        <AuthShell heading="Confirma a tua identidade" description="Introduz o código atual da tua aplicação autenticadora para continuar.">
            <h1>Código de autenticação</h1>
            <form method="POST" action="/two-factor/challenge">
                <CsrfField token={state.csrfToken} />
                <label htmlFor="code">Código de 6 dígitos</label>
                <input id="code" type="text" name="code" inputMode="numeric" pattern="[0-9]{6}" maxLength="6" autoComplete="one-time-code" required autoFocus />
                <FieldError errors={state.errors} name="code" />
                <button className="btn" type="submit">Verificar código</button>
            </form>
            <p><a href="/login">Voltar ao início de sessão</a></p>
        </AuthShell>
    );
}

export function TwoFactorSetupPage({ secret, qrCode, state }) {
    return (
        <AuthShell heading="Autenticação protegida" description="Configura uma aplicação autenticadora para proteger o acesso à tua conta.">
            <p>Configura uma aplicação autenticadora para proteger o acesso à tua conta.</p>
            <h1>Configurar segundo fator</h1>
            <p>Lê este código QR com a tua aplicação autenticadora.</p>
            <div className="codigo-qr" aria-label="Código QR para configurar a autenticação" dangerouslySetInnerHTML={{ __html: qrCode }} />
            <label htmlFor="secret">Chave de configuração</label>
            <input id="secret" value={secret} readOnly />
            <form method="POST" action="/two-factor/setup">
                <CsrfField token={state.csrfToken} />
                <label htmlFor="code">Código de 6 dígitos</label>
                <input id="code" type="text" name="code" inputMode="numeric" pattern="[0-9]{6}" maxLength="6" autoComplete="one-time-code" required autoFocus />
                <FieldError errors={state.errors} name="code" />
                <button className="btn" type="submit">Confirmar configuração</button>
            </form>
        </AuthShell>
    );
}
