import React, { useState, useEffect } from "react";
import "./App.css"
import Header from "./components/header.jsx";
import Body from "./components/body.jsx";
import Product from "./components/Product.jsx";
import "./components/header.css";
import "./components/body.css";
import { Routes, Route } from 'react-router-dom';
import { placeOrder as placeOrderMutation } from './components/Queries/orderMutations';


function App() {
  const [cartOpen, setCartOpen] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);
  
  const [activeCategory, setActiveCategory] = useState(() => {
    try {
      return localStorage.getItem('activeCategory') || '';
    } catch (e) {
      return '';
    }
  });

  const [cartItems, setCartItems] = useState(() => {
    try {
      const saved = localStorage.getItem('cartItems');
      return saved ? JSON.parse(saved) : [];
    } catch (e) {
      return [];
    }
  });

  const addToCart = (product, selectedAttributes) => {
    setCartItems(prev => {
      const existingIndex = prev.findIndex(
        item =>
          item.product_id === product.product_id &&
          JSON.stringify(item.selectedAttributes) === JSON.stringify(selectedAttributes)
      );

      let newCart;
      if (existingIndex >= 0) {
        newCart = prev.map((item, index) =>
          index === existingIndex
            ? { ...item, quantity: item.quantity + 1 }
            : item
        );
      } else {
        newCart = [...prev, { ...product, selectedAttributes, quantity: 1 }];
      }

      try {
        localStorage.setItem('cartItems', JSON.stringify(newCart));
      } catch (e) {
        console.error('Failed to save cart to localStorage', e);
      }

      return newCart;
    });
    setCartOpen(true);
  };

  useEffect(() => {
    try {
      localStorage.setItem('cartItems', JSON.stringify(cartItems));
    } catch (e) {
      console.error('Failed to save cart to localStorage', e);
    }
  }, [cartItems]);

  useEffect(() => {
    try {
      if (activeCategory) {
        localStorage.setItem('activeCategory', activeCategory);
      }
    } catch (e) {}
  }, [activeCategory]);

  const increaseQuantity = (index) => {
    setCartItems(prev => {
      const newCart = [...prev];
      newCart[index] = { ...newCart[index], quantity: (newCart[index].quantity || 1) + 1 };
      return newCart;
    });
  };

  const decreaseQuantity = (index) => {
    setCartItems(prev => {
      const newCart = [...prev];
      const currentQty = newCart[index].quantity || 1;
      if (currentQty > 1) {
        newCart[index] = { ...newCart[index], quantity: currentQty - 1 };
      } else {
        newCart.splice(index, 1);
      }
      return newCart;
    });
  };

  const handlePlaceOrder = async () => {
    if (!cartItems?.length) return;

    const orderItems = cartItems.map(item => ({
      product_id: item.product_id,
      item_name: item.product_name || item.name || 'Unknown',
      item_quantity: item.quantity || 1,
      item_attributes: JSON.stringify(item.selectedAttributes || item.item_attributes || {}),
    }));

    try {
      const result = await placeOrderMutation(orderItems);
      result?.success
        ? (setCartItems([]), setCartOpen(false))
        : console.error('❌ Order failed:', result?.message || 'Unknown error');
    } catch (err) {
      console.error('GraphQL error:', err);
    }
  };

  const handleQuickAdd = (product, e) => {
    e.preventDefault();
    e.stopPropagation();

    if (!product.in_stock) return;

    const defaultAttributes = {};
    if (product.attributes && product.attributes.length > 0) {
      product.attributes.forEach(attr => {
        if (!defaultAttributes[attr.set_name]) {
          defaultAttributes[attr.set_name] = attr.display_value || attr.value;
        }
      });
    }

    addToCart(
      {
        ...product,
        quantity: 1
      },
      defaultAttributes
    );
    setCartOpen(true);
  };

  const toggleCart = () => {
    setCartOpen(prev => !prev);
    setMenuOpen(false);
  };

  const toggleMenu = () => {
    setMenuOpen(prev => !prev);
    setCartOpen(false);
  };


  return (
    <>
      <Header
        activeCategory={activeCategory}
        setActiveCategory={setActiveCategory}
        cartItems={cartItems}
        setCartItems={setCartItems}
        cartOpen={cartOpen}
        setCartOpen={setCartOpen}
        decreaseQuantity={decreaseQuantity}
        increaseQuantity={increaseQuantity}
        handlePlaceOrder={handlePlaceOrder}
        menuOpen={menuOpen}
        setMenuOpen={setMenuOpen}
        toggleCart={toggleCart}
        toggleMenu={toggleMenu}
      />
      <Routes>
        <Route path="/" element={
          <Body
            activeCategory={activeCategory}
            addToCart={addToCart}
            setCartOpen={setCartOpen}
            handleQuickAdd={handleQuickAdd}
          />}
        />
        <Route path="/product/:id" element={
          <Product
            addToCart={addToCart}
            setCartOpen={setCartOpen}
          />}
        />
      </Routes>
    </>
  );
}
export default App;
