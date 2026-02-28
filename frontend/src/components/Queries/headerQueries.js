import { requestGraphQL } from './api';

export async function loadCategories() {
  const query = `query { categories { id name } }`;
  const { categories } = await requestGraphQL(query);
  return categories;
}