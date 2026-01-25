export default function Welcome({ name }) {
    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="text-center">
                <h1 className="text-4xl font-bold text-gray-800 mb-4">
                    Bienvenido a Inertia.js + React
                </h1>
                <p className="text-gray-600">
                    {name ? `Hola, ${name}!` : 'Tu aplicación está configurada correctamente.'}
                </p>
            </div>
        </div>
    );
}
