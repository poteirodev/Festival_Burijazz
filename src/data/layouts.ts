export type Seat = {
  id: string;
  row: string;
  number: number;
  side: "left" | "right";
  x: number;
  y: number;
  zone: string;
  price: number;
};

export type SeatLayout = {
  id: string;
  name: string;
  venue: string;
  stageLabel: string;
  width: number;
  height: number;
  seats: Seat[];
};

const rowsTop = ["A", "B", "C", "D", "E", "F", "G", "H", "I"];
const rowsMiddle = ["J", "K"];
const rowsBottom = ["L", "M", "N", "O", "P"];

const leftNumbers = [16, 14, 12, 10, 8, 6, 4, 2];
const rightNumbers = [1, 3, 5, 7, 9, 11, 13, 15];

function buildCasaCulturaSeats(): Seat[] {
  const seats: Seat[] = [];

  const leftStartX = 240;
  const rightStartX = 1010;
  const startY = 250;
  const rowGap = 82;
  const seatGap = 72;

  function addBlock(rows: string[], startRowIndex: number) {
    rows.forEach((row, localIndex) => {
      const y = startY + (startRowIndex + localIndex) * rowGap;

      leftNumbers.forEach((num, i) => {
        seats.push({
          id: `${num}${row}`,
          row,
          number: num,
          side: "left",
          x: leftStartX + i * seatGap,
          y,
          zone: "General",
          price: 120,
        });
      });

      rightNumbers.forEach((num, i) => {
        seats.push({
          id: `${num}${row}`,
          row,
          number: num,
          side: "right",
          x: rightStartX + i * seatGap,
          y,
          zone: "General",
          price: 120,
        });
      });
    });
  }

  addBlock(rowsTop, 0);
  addBlock(rowsMiddle, rowsTop.length);
  addBlock(rowsBottom, rowsTop.length + rowsMiddle.length + 1);

  return seats;
}

export const layouts: Record<string, SeatLayout> = {
  "casa-cultura-1": {
    id: "casa-cultura-1",
    name: "Casa de la Cultura - Distribución 1",
    venue: "Casa de la Cultura",
    stageLabel: "Pantalla",
    width: 1850,
    height: 1650,
    seats: buildCasaCulturaSeats(),
  },
};