import { requestGraphQL } from './api';
import { loadAllProducts } from './bodyQueries';

export async function loadProductById(id) {
  const list = await loadAllProducts();
  return list.find(p => String(p.product_id) === String(id)) || null;
}
