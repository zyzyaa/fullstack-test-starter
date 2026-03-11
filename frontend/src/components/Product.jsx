import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import './product.css';
import parse from 'html-react-parser';
import { loadProductById } from './Queries/productQueries';


export default function Product({ addToCart, setCartOpen }) {
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [selectedAttributes, setSelectedAttributes] = useState({});
  const handleSelect = (setName, attrValue) =>{
    setSelectedAttributes(prev => ({
      ...prev,
      [setName]: attrValue
    }));
  };


  useEffect(() => {
    async function load() {
      setLoading(true);
      try {
        const found = await loadProductById(id);
        setProduct(found);
        setSelectedImageIndex(0);
      } catch (e) {
        console.error(e);
      } finally {
        setLoading(false);
      }
    }
    load();
  }, [id]);

  const prevImage = () => {
    setSelectedImageIndex((prev) => 
      prev === 0 ? product.gallery.length - 1 : prev - 1
    );
  };

  const nextImage = () => {
    setSelectedImageIndex((prev) => 
      prev === product.gallery.length - 1 ? 0 : prev + 1
    );
  };


  if (loading) return <p>Loading...</p>;
  if (!product) return (
    <div style={{padding:20}}>
      <p>Product not found</p>
      <Link to="/">Back</Link>
    </div>
  );

  const allAttributesSelected =
    product.attributes && product.attributes.length > 0
      ? Object.keys(
          product.attributes.reduce((acc, attr) => {
            if (!acc[attr.set_name]) acc[attr.set_name] = [];
            acc[attr.set_name].push(attr);
            return acc;
          }, {})
        ).every(setName => selectedAttributes[setName])
      : true;


return (
  <div className="body">
    <div className="product-box">
      <div className="product-gallery-wrap">

        <div
          className={`product-gallery ${
            product.gallery && product.gallery.length > 5 ? 'scrollable' : ''
          }`}
          data-testid="product-gallery"
        >
          {product.gallery && product.gallery.length > 0 ? (
            product.gallery.map((g, i) => (
              <button
                key={i}
                type="button"
                className={`product-thumb ${i === selectedImageIndex ? 'product-thumb--active' : ''}`}
                onClick={() => setSelectedImageIndex(i)}
              >
                <img src={g.image_url} alt={`${product.product_name} ${i + 1}`} />
              </button>
            ))
          ) : (
            <div className="product-thumb--placeholder">No images</div>
          )}
        </div>

        <div className="product-main">
          <div className="product-main-image-box">

            {product.gallery?.length > 1 && (
              <button className="image-arrow left" onClick={prevImage}>
                ‹
              </button>
            )}

            {product.gallery && product.gallery.length > 0 ? (
              <img
                className="product-main-image"
                src={product.gallery[selectedImageIndex]?.image_url}
                alt={product.product_name}
              />
            ) : (
              <div className="no-image">No image</div>
            )}

            {product.gallery?.length > 1 && (
              <button className="image-arrow right" onClick={nextImage}>
                ›
              </button>
            )}

          </div>
          <div className="description-box">
            <div className="product-title">
              {product.product_name}
            </div>


          {product.attributes && product.attributes.length > 0 && (
            <div className="product-attributes">
              {Object.entries(
                product.attributes.reduce((acc, attr) => {
                  if (!acc[attr.set_name]) acc[attr.set_name] = [];
                  acc[attr.set_name].push(attr);
                  return acc;
                }, {})
              )
                .sort(([setNameA], [setNameB]) => {
                  if (setNameA.toLowerCase() === 'color') return 1;
                  if (setNameB.toLowerCase() === 'color') return -1;
                  return 0;
                })
                .map(([setName, attrs]) => (
                  <div
                    key={setName}
                    className="attribute-group"
                    data-testid={`product-attribute-${setName
                      .toLowerCase()
                      .replace(/\s+/g, '-')}`}
                  >
                    <h3 className="attribute-title">{setName}:</h3>
                    <div className="attribute-options">
                      {attrs.map((attr, index) => {
                        const value = attr.display_value || attr.value;
                        const isSelected = selectedAttributes[setName] === value;
                        const isColor = setName.toLowerCase() === 'color';
                        const normalizedTestIdValue = (() => {
                          if (!isColor) return String(value).replace(/\s+/g, '-');

                          const raw = String(attr.value ?? '').trim();
                          const withHash = raw.startsWith('#') ? raw : `#${raw}`;
                          if (/^#[0-9a-f]{6}$/i.test(withHash)) return withHash.toUpperCase();

                          return String(attr.display_value || value).replace(/\s+/g, '-');
                        })();

                        return (
                          <button
                            key={index}
                            className={`attribute-button ${isColor ? 'color-swatch' : ''} ${
                              isSelected ? 'selected' : ''
                            }`}
                            style={isColor ? { backgroundColor: attr.value } : {}}
                            onClick={() => handleSelect(setName, value)}
                            data-testid={`product-attribute-${setName
                              .toLowerCase()
                              .replace(/\s+/g, '-')}-${normalizedTestIdValue}`}
                          >
                            {!isColor && value}
                          </button>
                        );
                      })}
                    </div>
                  </div>
                ))}
            </div>
          )}

            {product.prices?.[0] && (
              <div className="product-price-block">
                <div className="price-title">PRICE:</div>
                <div className="product-price">
                  {product.prices[0].symbol}{product.prices[0].amount}
                </div>
              </div>
            )}

            <button
              className={`add-to-cart-button ${!allAttributesSelected ? 'disabled' : ''}`}
              data-testid='add-to-cart'
              onClick={() => 
                {
                if (allAttributesSelected) {
                  addToCart(
                    {
                      ...product,
                      quantity: 1
                    },
                    selectedAttributes
                  );
                  setCartOpen(true); 
                }
              }}
              disabled={!allAttributesSelected || !product.in_stock}
            >
              ADD TO CART
            </button>

            <div className="product-description" data-testid="product-description">
              {product.description 
                ? parse(product.description)
                : <em>No description.</em>
              }
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
);
}
