export function formatDateTimeLocal(isoString: string | undefined | null): string {
  if (isoString == null) return '';
  const date = new Date(isoString);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleString(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
}
