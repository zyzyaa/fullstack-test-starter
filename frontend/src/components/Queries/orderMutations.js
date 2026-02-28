import { requestGraphQL } from './api';

export async function placeOrder(items) {
  const mutation = `
    mutation PlaceOrder($items: [OrderItemInput!]!) {
      placeOrder(items: $items) {
        success
        order_id
        message
      }
    }
  `;
  const { placeOrder } = await requestGraphQL(mutation, { items });
  return placeOrder;
}
