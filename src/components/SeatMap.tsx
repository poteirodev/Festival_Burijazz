import { useMemo, useState } from "react";

type Seat = {
  id: string;
  row: string;
  number: number;
  side: "left" | "right";
  x: number;
  y: number;
  zone: string;
  price: number;
};

type Layout = {
  id: string;
  name: string;
  venue: string;
  stageLabel: string;
  width: number;
  height: number;
  seats: Seat[];
};

type Props = {
  eventTitle: string;
  basePrice: number;
  layout: Layout;
};

const MAX_SELECTION = 4;

export default function SeatMap({ eventTitle, basePrice, layout }: Props) {
  const [selectedSeats, setSelectedSeats] = useState<string[]>([]);

  const selectedSeatObjects = useMemo(
    () => layout.seats.filter((seat) => selectedSeats.includes(seat.id)),
    [layout.seats, selectedSeats]
  );

  const total = selectedSeatObjects.reduce(
    (sum, seat) => sum + (seat.price || basePrice),
    0
  );

  const toggleSeat = (seatId: string) => {
    setSelectedSeats((prev) => {
      if (prev.includes(seatId)) {
        return prev.filter((id) => id !== seatId);
      }

      if (prev.length >= MAX_SELECTION) {
        return prev;
      }

      return [...prev, seatId];
    });
  };

  const uniqueRows = [...new Set(layout.seats.map((seat) => seat.row))];

  const renderMapContent = () => (
    <>
      <div
        className="absolute left-1/2 -translate-x-1/2"
        style={{
          top: "42px",
          width: "88%",
          height: "90px",
        }}
      >
        <div className="absolute inset-x-0 top-0 h-[6px] rounded-full bg-[#555]" />
        <div
          className="absolute inset-x-0 top-[6px] mx-auto h-[52px]"
          style={{
            width: "100%",
            background:
              "linear-gradient(to bottom, rgba(0,0,0,0.18), rgba(0,0,0,0.02))",
            clipPath: "polygon(0 0, 100% 0, 96% 100%, 4% 100%)",
          }}
        />
        <p className="absolute inset-x-0 top-[-10px] text-center text-2xl font-medium uppercase tracking-[0.12em] text-[#353535]">
          {layout.stageLabel}
        </p>
      </div>

      <div
        className="absolute top-[170px] bottom-[80px] left-1/2 -translate-x-1/2 bg-white/80"
        style={{ width: "90px" }}
      />

      {uniqueRows.map((row) => {
        const rowSeats = layout.seats.filter((seat) => seat.row === row);
        const rowY = rowSeats[0]?.y ?? 0;

        return (
          <div
            key={row}
            className="absolute right-[36px] -translate-y-1/2 text-2xl text-[#5f5f5f]"
            style={{ top: `${rowY}px` }}
          >
            {row}
          </div>
        );
      })}

      {layout.seats.map((seat) => {
        const isSelected = selectedSeats.includes(seat.id);

        return (
          <div
            key={seat.id}
            className="absolute -translate-x-1/2 -translate-y-1/2"
            style={{
              left: `${seat.x}px`,
              top: `${seat.y}px`,
            }}
          >
            <button
              type="button"
              onClick={() => toggleSeat(seat.id)}
              title={seat.id}
              className={`h-9 w-9 rounded-[6px] text-[14px] font-bold shadow-sm transition ${
                isSelected
                  ? "bg-[#8dc26f] text-white"
                  : "bg-[#9c9c9c] text-white hover:bg-[#7d7d7d]"
              }`}
            >
              {seat.number}
            </button>
          </div>
        );
      })}
    </>
  );

  return (
    <div className="grid gap-4 xl:grid-cols-[minmax(0,1fr)_340px] xl:items-start">
      <section className="rounded-2xl border border-white/10 bg-[#18204a] p-3 md:p-5">
        <div className="mb-4 flex flex-col gap-3">
          <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h2 className="text-sm font-black uppercase tracking-[0.2em] text-[#f4eed7] md:text-base">
              Plano de sala
            </h2>

            <div className="flex flex-wrap gap-3 text-xs text-white/85">
              <div className="flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5">
                <span className="h-3.5 w-3.5 rounded bg-[#7f8da3]"></span>
                <span>Disponible</span>
              </div>
              <div className="flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5">
                <span className="h-3.5 w-3.5 rounded bg-[#8dc26f]"></span>
                <span>Tu selección</span>
              </div>
              <div className="flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5">
                <span className="h-3.5 w-3.5 rounded bg-[#b6322b]"></span>
                <span>Ocupado</span>
              </div>
              <div className="flex items-center gap-2 rounded-full bg-white/5 px-3 py-1.5">
                <span className="h-3.5 w-3.5 rounded bg-[#d9d9d9]"></span>
                <span>No disponible</span>
              </div>
            </div>
          </div>

          <div className="rounded-xl border border-[#f0c85a]/20 bg-[#10183f] px-4 py-3 text-sm text-white/75">
            Máximo {MAX_SELECTION} asientos por compra.
          </div>
        </div>

        <div className="rounded-xl border border-white/8 bg-[#11193f] p-3">
          <div className="rounded-xl bg-[#e8e8e8]">
            <div className="overflow-auto lg:hidden">
              <div
                className="relative"
                style={{
                  width: `${layout.width}px`,
                  height: `${layout.height}px`,
                  minWidth: `${layout.width}px`,
                  minHeight: `${layout.height}px`,
                }}
              >
                {renderMapContent()}
              </div>
            </div>

            <div className="hidden overflow-hidden lg:flex lg:justify-center lg:p-4">
              <div className="origin-top scale-[0.68] xl:scale-[0.78] 2xl:scale-[0.9]">
                <div
                  className="relative"
                  style={{
                    width: `${layout.width}px`,
                    height: `${layout.height}px`,
                  }}
                >
                  {renderMapContent()}
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <aside className="hidden rounded-2xl border border-white/10 bg-[#1d285d] p-6 shadow-[8px_8px_0_rgba(8,12,31,0.35)] xl:block">
        <p className="text-[11px] uppercase tracking-[0.35em] text-white/55">
          Resumen
        </p>

        <h2 className="mt-4 text-2xl font-black uppercase text-[#f0c85a]">
          {eventTitle}
        </h2>

        <div className="mt-6 rounded-xl border border-white/8 bg-[#10183f] p-4">
          <p className="text-xs uppercase tracking-[0.25em] text-white/50">
            Asientos seleccionados
          </p>

          <div className="mt-3 flex flex-wrap gap-2">
            {selectedSeatObjects.length > 0 ? (
              selectedSeatObjects.map((seat) => (
                <span
                  key={seat.id}
                  className="rounded-full bg-[#8dc26f] px-3 py-1 text-xs font-black text-white"
                >
                  {seat.id}
                </span>
              ))
            ) : (
              <p className="text-sm text-white/60">Aún no seleccionaste asientos.</p>
            )}
          </div>
        </div>

        <div className="mt-6 flex items-center justify-between border-t border-white/10 pt-6">
          <span className="text-sm uppercase tracking-[0.2em] text-white/65">
            Total
          </span>
          <span className="text-3xl font-black text-[#f0c85a]">Bs {total}</span>
        </div>

        <button
          type="button"
          disabled={selectedSeatObjects.length === 0}
          className="mt-6 w-full rounded-xl bg-[#f4eed7] px-6 py-4 text-sm font-black uppercase tracking-[0.18em] text-[#121a4a] transition disabled:cursor-not-allowed disabled:opacity-50"
        >
          Continuar compra
        </button>
      </aside>

      <div className="fixed inset-x-0 bottom-0 z-50 border-t border-white/10 bg-[#0f1535]/95 px-4 py-3 backdrop-blur md:px-5 xl:hidden">
        <div className="mx-auto max-w-5xl">
          <div className="mb-3 flex flex-wrap gap-2">
            {selectedSeatObjects.length > 0 ? (
              selectedSeatObjects.map((seat) => (
                <span
                  key={seat.id}
                  className="rounded-full bg-[#8dc26f] px-3 py-1 text-xs font-black text-white"
                >
                  {seat.id}
                </span>
              ))
            ) : (
              <span className="text-sm text-white/65">Selecciona tus asientos</span>
            )}
          </div>

          <div className="flex items-center gap-3">
            <div className="min-w-0 flex-1">
              <p className="text-[11px] uppercase tracking-[0.25em] text-white/50">
                Total
              </p>
              <p className="text-2xl font-black text-[#f0c85a]">Bs {total}</p>
            </div>

            <button
              type="button"
              disabled={selectedSeatObjects.length === 0}
              className="rounded-xl bg-[#f4eed7] px-5 py-3 text-sm font-black uppercase tracking-[0.14em] text-[#121a4a] transition disabled:cursor-not-allowed disabled:opacity-50"
            >
              Continuar
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}