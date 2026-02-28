import { requestGraphQL } from './api';

const productFields = `
  product_id
  product_name
  description
  in_stock
  prices { amount label symbol }
  gallery { image_url }
  category_name
  attributes { set_name value display_value }
`;

export async function loadAllProducts() {
  const query = `query { products { ${productFields} } }`;
  const { products } = await requestGraphQL(query);
  return products;
}

export async function loadProductsByCategory(category) {
  const query = `
    query ($category: String!) {
      productsByCategory(categoryName: $category) { ${productFields} }
    }`;
  const { productsByCategory } = await requestGraphQL(query, { category });
  return productsByCategory;
}
