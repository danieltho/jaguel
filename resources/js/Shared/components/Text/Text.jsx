export default function Text({ type = 'hero', title, subtitle, className = '' }) {
    if (type === 'title') {
        return (
            <div className={`flex flex-col items-center justify-center w-full ${className}`}>
                <p className="w-full text-center text-white text-[40px] font-semibold leading-none">
                    {title}
                </p>
            </div>
        );
    }

    if (type === 'subtitle') {
        return (
            <div className={`flex flex-col items-center justify-center w-full ${className}`}>
                <p className="w-full text-white text-[32px] font-medium leading-none">
                    {title}
                </p>
            </div>
        );
    }

    return (
        <div className={`flex flex-col gap-[10px] w-full ${className}`}>
            <p className="w-full text-white text-[40px] font-semibold leading-none">
                {title}
            </p>
            {subtitle && (
                <p className="w-full text-[#f0f0f3] text-[20px] font-semibold leading-none">
                    {subtitle}
                </p>
            )}
        </div>
    );
}
