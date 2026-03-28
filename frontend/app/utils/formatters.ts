export function formatDate(dateStr: string | undefined): string {
  if (!dateStr) return 'Unbekannt'
  const date = new Date(dateStr)
  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()
  return `${day}.${month}.${year}`
}

export function getPetEmoji(species: string | undefined | null): string {
  if (!species) return '🐾'
  const s = species.toLowerCase()
  if (s.includes('dog') || s.includes('hund')) return '🐶'
  if (s.includes('cat') || s.includes('katz')) return '🐱'
  if (s.includes('rabbit') || s.includes('hase') || s.includes('kanin')) return '🐰'
  return '🐾'
}

export function getAge(birthDateString: string | undefined | null): string | number {
  if (!birthDateString) return '--'
  const birthDate = new Date(birthDateString)
  if (isNaN(birthDate.getTime())) return '--'
  const diff = Date.now() - birthDate.getTime()
  const ageDate = new Date(Math.max(0, diff))
  const years = Math.abs(ageDate.getUTCFullYear() - 1970)
  return years === 0 ? '< 1' : years
}
