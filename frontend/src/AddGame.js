import React, { useState } from 'react';
import axios from 'axios';

const AddGame = () => {
  const [userId, setUserId] = useState('');
  const [gameId, setGameId] = useState('');
  const [status, setStatus] = useState('');
  const [platformId, setPlatformId] = useState('');
  const [message, setMessage] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();

    const data = {
      user_id: parseInt(userId),
      game_id: parseInt(gameId),
      status: status,
      platform_id: parseInt(platformId),
    };

    try {
      const response = await axios.post('http://localhost:8000/api/collection', data);
      setMessage('Game added successfully');
    } catch (error) {
        console.log(error);
      setMessage('Error: ' + error.response.data.message);
    }
  };

  return (
    <div>
      <h2>Add a Game</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>User ID:</label>
          <input type="text" value={userId} onChange={(e) => setUserId(e.target.value)} />
        </div>
        <div>
          <label>Game ID:</label>
          <input type="text" value={gameId} onChange={(e) => setGameId(e.target.value)} />
        </div>
        <div>
          <label>Status:</label>
          <input type="text" value={status} onChange={(e) => setStatus(e.target.value)} />
        </div>
        <div>
          <label>Platform ID:</label>
          <input type="text" value={platformId} onChange={(e) => setPlatformId(e.target.value)} />
        </div>
        <button type="submit">Add Game</button>
      </form>
      {message && <p>{message}</p>}
    </div>
  );
};

export default AddGame;
