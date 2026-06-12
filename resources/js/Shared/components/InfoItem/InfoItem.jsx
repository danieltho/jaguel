export default function InfoItem({ icon, title, description }) {
    return (
        <div className="flex gap-4 items-center px-2.5 py-2 rounded-2xl w-full sm:w-auto sm:flex-1 min-w-0">
            <div className="shrink-0 text-neutral-500">{icon}</div>
            <div className="flex flex-col gap-[5px] text-neutral-500 leading-normal min-w-0 wrap-break-word">
                <p className="text-sm uppercase font-bold">{title}</p>
                <p className="text-sm font-normal">{description}</p>
            </div>
        </div>
    );
}
