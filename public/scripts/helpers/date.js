export function formatDate(date, showTime = false) {
  const pad = (n) => n.toString().padStart(2, '0');

  const day = pad(date.getDate());
  const month = pad(date.getMonth() + 1);
  const year = date.getFullYear();

  let formatted = `${day}/${month}/${year}`;

  if (showTime) {
    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    // const seconds = pad(date.getSeconds());
    formatted += ` ${hours}:${minutes}`;
  }

  return formatted;
}
