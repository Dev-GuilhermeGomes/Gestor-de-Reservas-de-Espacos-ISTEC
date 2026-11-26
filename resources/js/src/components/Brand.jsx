export default function Brand({ inverse = false, compact = false }) {
    const className = [
        'marca',
        inverse ? 'marca-inversa' : '',
        compact ? 'marca-compacta' : '',
    ].filter(Boolean).join(' ');

    return (
        <div className={className}>
            <svg className="marca-simbolo" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
                <path d="M37 11H20a9 9 0 0 0-9 9v22a9 9 0 0 0 9 9h11" />
                <path className="marca-confirmacao" d="m32 37 8 8 15-18" />
            </svg>
            <span className="marca-nome">
                <strong>Gestor de Reservas</strong>
                <span>de Espaços</span>
            </span>
        </div>
    );
}
