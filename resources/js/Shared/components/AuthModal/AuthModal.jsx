import { Link } from '@inertiajs/react';
import { Dialog, DialogPanel, DialogTitle } from '@headlessui/react';

export default function AuthModal({ isOpen, onClose }) {
    return (
        <Dialog open={isOpen} onClose={onClose} className="relative z-50">
            <div className="fixed inset-0 bg-black/40" aria-hidden="true" />

            <div className="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel className="bg-white rounded-2xl p-8 max-w-sm w-full text-center">
                    <DialogTitle className="text-2xl font-extrabold text-neutral-500 mb-2">
                        ¿Tiene una Cuenta?
                    </DialogTitle>
                    <p className="text-neutral-400 mb-6">
                        Por favor, registre su cuenta o inicie sesión para poder continuar con el pedido.
                    </p>

                    <div className="flex flex-col gap-3">
                        <Link
                            href="/cuenta/registro"
                            className="w-full py-3 bg-neutral-500 text-white font-medium rounded-full hover:bg-neutral-400 transition-colors block"
                        >
                            Registrarse
                        </Link>
                        <Link
                            href="/cuenta/login"
                            className="w-full py-3 border border-neutral-500 text-neutral-500 font-medium rounded-full hover:bg-neutral-50 transition-colors block"
                        >
                            Iniciar Sesión
                        </Link>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}
