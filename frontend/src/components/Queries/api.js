export async function requestGraphQL(query, variables = {}) {
  const res = await fetch('/graphql', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ query, variables }),
  });
  const data = await res.json();
  if (data.errors) {
    throw new Error(data.errors.map(e => e.message).join(', '));
  }
  return data.data;
}
