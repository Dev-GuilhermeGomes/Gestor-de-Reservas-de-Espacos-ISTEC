export function DashboardPage({ state }) {
    return (
        <div className="cliente-dashboard">
            <section className="cliente-hero">
                <div className="cliente-hero-copy">
                    <span className="cliente-eyebrow">RESERVAR. SIMPLIFICAR. AVANÇAR.</span>
                    <h2>O espaço certo.<br />À hora certa.</h2>
                    <p>Olá, {state.auth?.nome}. Encontra o espaço ideal para estudar, reunir e criar.</p>
                    <a className="botao botao-principal cliente-cta" href="/utilizador/reservar">Encontrar um espaço</a>
                </div>
                <div className="cliente-hero-visual" aria-hidden="true">
                    <svg viewBox="0 0 480 340" role="presentation">
                        <rect width="480" height="340" rx="18" fill="#e8f1f2" />
                        <path d="M25 35h185v205H25z" fill="#cde4e5" />
                        <path d="M38 48h78v177H38zm91 0h68v177h-68z" fill="#f8fbfa" />
                        <path d="M116 48v177m13-88h68" stroke="#9bbdc1" strokeWidth="5" />
                        <path d="M0 248h480v92H0z" fill="#d6e5e3" />
                        <path d="M0 274h480M0 305h480" stroke="#c1d5d3" strokeWidth="2" />
                        <path d="M213 150h220v20H213z" fill="#0b293d" />
                        <path d="M230 170v77m184-77v77" stroke="#0b293d" strokeWidth="8" />
                        <path d="M240 132h164v18H240z" fill="#17c5b2" />
                        <path d="M252 193h30v45h-30zm134 0h30v45h-30z" fill="#08789b" />
                        <path d="M244 238h46m90 0h46" stroke="#0b293d" strokeWidth="7" strokeLinecap="round" />
                        <path d="M225 116h194" stroke="#fff" strokeWidth="8" strokeLinecap="round" />
                        <circle cx="360" cy="68" r="20" fill="#17c5b2" opacity=".8" />
                    </svg>
                    <span>ESPAÇOS PARA AS TUAS IDEIAS</span>
                </div>
            </section>
            <section className="cliente-passos" aria-label="Como reservar um espaço">
                <article>
                    <span>01</span>
                    <h3>Escolhe o espaço</h3>
                    <p>Descobre salas adequadas ao que precisas.</p>
                </article>
                <article>
                    <span>02</span>
                    <h3>Indica o horário</h3>
                    <p>Escolhe a data e o período pretendido.</p>
                </article>
                <article>
                    <span>03</span>
                    <h3>Acompanha o pedido</h3>
                    <p>Consulta o estado dos teus pedidos no painel.</p>
                </article>
            </section>
        </div>
    );
}

export function AdminPage() {
    return (
        <section className="cartao">
            <h2>Área da Administração</h2>
            <p>Em construção.</p>
        </section>
    );
}

export function ReservationPage() {
    return (
        <section className="cartao">
            <h2>Reservar Espaço</h2>
            <p>Formulário para reservar um espaço.</p>
        </section>
    );
}
