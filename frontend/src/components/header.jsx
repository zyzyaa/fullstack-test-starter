import React, { useEffect, useState, useRef } from 'react';
import { Link } from 'react-router-dom';
import './header.css';
import logo from '../assets/a-logo.png';
import cartIcon from '../assets/cart.png';
import { loadCategories } from './Queries/headerQueries';

const Header = ({ activeCategory, setActiveCategory, cartItems, cartOpen, setCartOpen, increaseQuantity, decreaseQuantity, handlePlaceOrder, toggleCart, toggleMenu, menuOpen, setMenuOpen }) => {
  const [categories, setCategories] = useState([]);
  const cartBoxRef = useRef(null);
  
  useEffect(() => {
  async function loadCategoriesList() {
    try {
      const names = (await loadCategories()).map(c => c.name);
      setCategories(names);
      if (!activeCategory && names.length > 0) setActiveCategory(names[0]);
    } catch (e) {
      console.error('Failed to load categories', e);
    }
  }
  loadCategoriesList();
  }, []);

  const handleHeaderClick = (e) => {
    if (!cartOpen) return;
    if (cartBoxRef.current && cartBoxRef.current.contains(e.target)) return;
    setCartOpen(false);
  };


  return (
    <>
      {cartOpen && (
        <div
          className="overlay"
          data-testid="cart-overlay"
          onClick={() => setCartOpen(false)}
        ></div>
      )}
    <header className="header" onClick={handleHeaderClick}>
      <button
        className={`burger ${menuOpen ? 'open' : ''}`}
        onClick={toggleMenu}
      >
        <span className='bar'></span>
        <span className='bar'></span>
        <span className='bar'></span>
      </button>

      <nav className={`nav ${menuOpen ? 'open' : ''}`}>
        {categories.map(category => (
          <div key={category} className="category-box">
            <div className="label-box">
              <Link
                to={`/${encodeURIComponent(category)}`}
                onClick={() => { setActiveCategory(category); setMenuOpen(false); }}
                className={activeCategory === category ? 'active-category-link' : 'category-link'}
                data-testid={activeCategory === category ? 'active-category-link' : 'category-link'}
              >
                {category}
              </Link>
            </div>
          </div>
        ))}
      </nav>

      <div className="logo-box">
        <Link 
          to="/all"
          onClick={() => setActiveCategory('all')}
        >
          <img src={logo} alt="Logo" className="logo"/>
        </Link>
      </div>

      <div className="cart-box" ref={cartBoxRef}>
        <button 
          className="cart-logo-button" 
          onClick={toggleCart}
          data-testid='cart-btn'
        >
          <div className="cart-icon-wrapper">
            <img src={cartIcon} alt="Shop cart" className="cart-logo"/>
            {cartItems.length > 0 && (
              <div className="cart-count-circle">
                {cartItems.reduce((total, item) => total + (item.quantity || 1), 0)}
              </div>
            )}
          </div>
        </button>

        {cartOpen && (
        <>
          <div className="cart-dropdown">
            <div className="cart-header">
              <div className="my-bag">My Bag,</div>
              <div className="item-count" data-testid='cart-item-amount'>
                {(() => {
                  const count = cartItems.reduce((total, item) => total + item.quantity, 0);
                  return count === 1 ? "1 Item" : `${count} Items`;
                })()}
              </div>
            </div>
            <div className="dropdown-scroll">

              
                {cartItems.length === 0 ? (
                  <p>Your cart is empty.</p>
                ) : (
                  <>
                    {cartItems.map((item, idx) => (
                      <div key={idx} className="cart-item">
                        <div className="cart-item-info">
                        <div className="cart-item-name">{item.product_name}</div>
                          {item.prices?.[0] && (
                            <div className="cart-item-price">
                              {item.prices[0].symbol}
                              {item.prices[0].amount}
                            </div>
                          )}
                        {item.attributes && item.attributes.length > 0 && (
                          <div className="cart-item-attributes">
                            {Object.entries(
                              item.attributes.reduce((acc, attr) => {
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
                              <div key={setName} className="cart-attribute-group" data-testid={`cart-item-attribute-${setName.toLowerCase().replace(/\s+/g, '-')}`}>
                                <h3 className="cart-attribute-title">{setName}:</h3>
                                <div className="cart-attribute-options">
                                  {attrs.map((attr, index) => {
                                    const value = attr.display_value || attr.value;
                                    const isSelected = item.selectedAttributes[setName] === value;
                                    const isColor = setName.toLowerCase() === 'color';

                                    return (
                                      <div
                                        key={index}
                                        className={`cart-attribute-button ${isColor ? 'color-swatch' : ''} ${
                                          isSelected ? 'selected' : ''
                                        }`}
                                        style={isColor ? { backgroundColor: attr.value } : {}}
                                        disabled
                                        data-testid={
                                          `cart-item-attribute-${setName.toLowerCase().replace(/\s+/g, '-')}-` +
                                          `${value.toLowerCase().replace(/\s+/g, '-')}` +
                                          (isSelected ? `-selected` : ``)
                                        }
                                      >
                                        {!isColor && value}
                                      </div>
                                    );
                                  })}
                                </div>
                              </div>
                            ))}
                          </div>
                        )}
                        </div>
                          <div className="cart-item-controls">
                            <button className="capacity-button" data-testid='cart-item-amount-increase' onClick={() => increaseQuantity(idx)} >+</button>
                            <span>{item.quantity}</span>
                            <button className="capacity-button" data-testid='cart-item-amount-decrease' onClick={() => decreaseQuantity(idx)}>-</button>
                          </div>
                        {item.gallery?.[0]?.image_url && (
                          <div className="cart-item-imagebox">
                            <img 
                              src={item.gallery[0].image_url} 
                              alt={item.product_name} 
                              className="cart-item-image"
                            />
                          </div>
                        )}
                      </div>
                    ))}
                </>
                )}
                </div>
                <div className="cart-bottom-box">
                  <div className="cart-total" data-testid='cart-total'>
                    <div className="total">Total:</div>
                    <div className="total-price">
                    {(() => {
                      const symbol = cartItems.length > 0 
                        ? (cartItems[0]?.prices?.[0]?.symbol || '$')
                        : '$';
                      const total = cartItems.reduce((sum, item) => {
                        const price = item.prices?.[0]?.amount || 0;
                        return sum + price * (item.quantity || 1);
                      }, 0);
                      return `${symbol}${total.toFixed(2)}`;
                    })()}
                    </div>
                  </div>

                  <button 
                    className={`place-order-button ${cartItems.length === 0 ? 'disabled' : ''}`}
                    onClick={handlePlaceOrder}
                    disabled={cartItems.length === 0}
                  >
                    Place order
                  </button>
                </div>
            </div>
          </>
        )}
      </div>
    </header>
    </>
  );
};

export default Header;
