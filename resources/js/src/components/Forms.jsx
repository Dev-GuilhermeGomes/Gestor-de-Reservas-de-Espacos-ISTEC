export function CsrfField({ token }) {
    return <input type="hidden" name="_token" value={token} />;
}

export function FieldError({ errors, name }) {
    const message = errors?.[name]?.[0];

    return message ? <p className="erro">{message}</p> : null;
}

export function ServerErrors({ errors }) {
    const message = Object.values(errors ?? {}).flat()[0];

    return message ? <p className="erro">{message}</p> : null;
}
