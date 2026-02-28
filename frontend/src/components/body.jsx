import { useEffect, useState } from 'react';
import './body.css';
import circle from '../assets/circle.png';
import { Link } from 'react-router-dom';
import { loadAllProducts, loadProductsByCategory } from './Queries/bodyQueries';


const Body = ({ activeCategory, handleQuickAdd }) => {
  const [products, setProducts] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    async function loadProducts() {
      setLoading(true);
      try {
        const list = activeCategory && activeCategory !== 'all'
          ? await loadProductsByCategory(activeCategory)
          : await loadAllProducts();
        setProducts(list);
      } catch (e) {
        console.error('Failed to load products', e);
      } finally {
        setLoading(false);
      }
    }
    loadProducts();
  }, [activeCategory]);
  

  return (
    <div className="body">
      <div className="title-box">
        <div className="title">{activeCategory}</div>
      </div>
      <div className="body-box">
        <div className="products-list">
          {loading ? (
            <p>Loading products...</p>
          ) : (
            products.map(product => (
              <div className="product-item" key={product.product_id} data-testid={`product-${product.product_name.toLowerCase().replace(/\s+/g, '-')}`}>
                <div className="product-image-box">
                  <Link to={`/product/${encodeURIComponent(product.product_id)}`}>
                  <img
                    src={product.gallery?.[0]?.image_url || "/placeholder.jpg"}
                    alt={product.product_name}
                    className={`product-image ${product.in_stock ? '' : 'out-of-stock'}`}
                  />
                    {!product.in_stock && (
                      <span className="out-of-stock-text">OUT OF STOCK</span>
                    )}
                  </Link>
                    {product.in_stock ? (
                      <img
                        src={circle}
                        alt="Add to cart"
                        className="circle-logo"
                        onClick={(e) => {
                          e.preventDefault();
                          e.stopPropagation();
                          handleQuickAdd(product, e);
                        }}

                      />
                    ) : null}
                  </div>
                <div className="product-name">{product.product_name}</div>
                {product.prices && product.prices.length > 0 && (
                  <div className={`price ${!product.in_stock ? 'out-of-stock' : ''}`}>
                    {product.prices[0].symbol}
                    {product.prices[0].amount}
                  </div>
                )}
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};

export default Body;