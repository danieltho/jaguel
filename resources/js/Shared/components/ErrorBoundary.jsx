import { ErrorBoundary as ReactErrorBoundary } from 'react-error-boundary';

function ErrorFallback({ error, resetErrorBoundary }) {
  return (
    <div className="flex flex-col items-center justify-center p-8 text-center">
      <h2 className="text-xl font-semibold text-red-600 mb-2">
        Algo salió mal
      </h2>
      <p className="text-gray-600 mb-4">
        {error.message}
      </p>
      <button
        onClick={resetErrorBoundary}
        className="px-4 py-2 bg-oxido-50 rounded-lg hover:bg-oxido-100"
      >
        Intentar de nuevo
      </button>
    </div>
  );
}

function ErrorBoundary({ children }) {
  const handleError = (error, info) => {
    // Aquí puedes enviar a Sentry, LogRocket, etc.
    console.error('Error capturado:', error);
    console.error('Component stack:', info.componentStack);
  };

  return (
    <ReactErrorBoundary
      FallbackComponent={ErrorFallback}
      onError={handleError}
      onReset={() => {
        // Lógica para resetear el estado si es necesario
      }}
    >
      {children}
    </ReactErrorBoundary>
  );
}

export default ErrorBoundary;
