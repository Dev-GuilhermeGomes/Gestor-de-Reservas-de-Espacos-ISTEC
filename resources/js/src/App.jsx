import { useEffect } from 'react';
import AppLayout from './components/AppLayout.jsx';
import { ChallengePage, LoginPage, TwoFactorSetupPage } from './pages/AuthPages.jsx';
import { AdminPage, DashboardPage, ReservationPage } from './pages/DashboardPages.jsx';
import {
    CreateRoomPage,
    CreateUnavailabilityPage,
    EditReservationPage,
    EditRoomPage,
    EditUnavailabilityPage,
    ManagerRequestsPage,
    OccupancyPage,
    RefusalPage,
    RefusalReasonPage,
    RoomsPage,
    UnavailabilitiesPage,
} from './pages/ManagerPages.jsx';
import { ServerErrors } from './components/Forms.jsx';

const pages = {
    'auth.login': LoginPage,
    'auth.two-factor-challenge': ChallengePage,
    'auth.two-factor-setup': TwoFactorSetupPage,
    dashboard: DashboardPage,
    admin: AdminPage,
    'user.reservation': ReservationPage,
    'manager.requests': ManagerRequestsPage,
    'manager.reject': RefusalPage,
    'manager.reason': RefusalReasonPage,
    'manager.edit-reservation': EditReservationPage,
    'manager.occupancy': OccupancyPage,
    'manager.unavailabilities': UnavailabilitiesPage,
    'manager.create-unavailability': CreateUnavailabilityPage,
    'manager.edit-unavailability': EditUnavailabilityPage,
    'manager.rooms': RoomsPage,
    'manager.create-room': CreateRoomPage,
    'manager.edit-room': EditRoomPage,
};

const pageTitles = {
    'auth.login': 'Entrar',
    'auth.two-factor-challenge': 'Verificação',
    'auth.two-factor-setup': 'Configurar autenticação',
    dashboard: 'Início',
    admin: 'Área da Administração',
    'user.reservation': 'Reservar espaço',
    'manager.requests': 'Pedidos de reserva',
    'manager.reject': 'Recusar reserva',
    'manager.reason': 'Motivo da recusa',
    'manager.edit-reservation': 'Editar reserva',
    'manager.occupancy': 'Ocupação global',
    'manager.unavailabilities': 'Indisponibilidades',
    'manager.create-unavailability': 'Criar indisponibilidade',
    'manager.edit-unavailability': 'Editar indisponibilidade',
    'manager.rooms': 'Salas',
    'manager.create-room': 'Criar sala',
    'manager.edit-room': 'Editar sala',
};

export default function App({ initialPage }) {
    const page = initialPage?.page;
    const Page = pages[page?.component];
    const title = pageTitles[page?.component] ?? 'Reservas ISTEC';

    useEffect(() => {
        document.title = `${title} · Reservas ISTEC`;
    }, [title]);

    if (!Page) {
        return <main className="cartao"><h1>Página não encontrada</h1></main>;
    }

    const state = {
        auth: initialPage.auth,
        csrfToken: initialPage.csrfToken,
        errors: initialPage.errors ?? {},
        flash: initialPage.flash ?? {},
        old: initialPage.old ?? {},
    };

    if (page.component.startsWith('auth.')) {
        return <Page {...page.props} state={state} />;
    }

    return (
        <AppLayout user={state.auth} state={state} title={title}>
            <Page {...page.props} state={state} />
        </AppLayout>
    );
}
