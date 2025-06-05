import React, { useEffect, useState } from 'react';
import { signRequest } from './utils';
import { useDarkMode } from './useDarkMode';

function App() {
  const [computers, setComputers] = useState([]);
  const [form, setForm] = useState({ marca: '', cpu: '', gpu: '', ram: '', disco: '', id: null });
  const [key, setKey] = useState(null);
  const [dark, setDark] = useDarkMode();

  const path = '/services-test/backend/api/services.php';

  // Obtener la llave una vez al inicio
  useEffect(() => {
    const fetchKey = async () => {
      const res = await fetch('http://192.168.1.199/services-test/backend/api/get_signing_key.php', {
        credentials: 'include'
      });
      const data = await res.json();
      setKey(data.signing_key);
    };
    fetchKey();
  }, []);

  // Configurar modo oscuro
  useEffect(() => {
    const prefersDark = localStorage.getItem("theme") === "dark";
    setDark(prefersDark);
    document.documentElement.classList.toggle("dark", prefersDark);
  }, []);

  useEffect(() => {
    document.documentElement.classList.toggle("dark", dark);
    localStorage.setItem("theme", dark ? "dark" : "light");
  }, [dark]);

  const callService = async (serviceName, params = {}, method = 'POST') => {
    if (!key) throw new Error("No se ha cargado la clave de firma");

    const timestamp = Date.now();
    const body = new URLSearchParams({ serviceName, ...params });
    const bodyStr = body.toString();

    const clientSignature = signRequest(path, timestamp, bodyStr, key);

    const res = await fetch(`http://192.168.1.199${path}`, {
      method,
      headers: {
        'X-Client-Timestamp': timestamp,
        'X-Client-Signature': /* "5b9308acf19f57cd2e610f601cefdaf3ea1bd8d7f45935440b7a531774d97d67" */clientSignature,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      credentials: 'include',
      body: bodyStr
    });

    const serverSignature = res.headers.get("X-Server-Signature");
    const text = await res.text();

    const expectedServerSig = signRequest(path, timestamp, text, key);
    if (serverSignature !== expectedServerSig) {
      //throw new Error("⚠️ Firma del servidor inválida. Respuesta posiblemente comprometida.");
    }

    return JSON.parse(text);
  };

  const getComputers = async () => {
    const data = await callService('get_all_computers');
    setComputers(data);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (form.id === null) {
      await callService('add_computer', form);
    } else {
      await callService('update_computer', form);
    }
    setForm({ marca: '', cpu: '', gpu: '', ram: '', disco: '', id: null });
    getComputers();
  };

  const handleEdit = (comp) => setForm(comp);

  const handleDelete = async (id) => {
    await callService('delete_computer', { id });
    getComputers();
  };

  return (
    <div className="min-h-screen bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-100 font-mono p-6 transition-colors">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold text-cyan-700 dark:text-cyan-400">💻 Lista de Computadoras</h1>
        <button
          onClick={() => setDark(!dark)}
          className="px-4 py-2 rounded bg-cyan-700 dark:bg-cyan-500 hover:opacity-90 text-white font-semibold"
        >
          {dark ? '☀️ Claro' : '🌙 Oscuro'}
        </button>
      </div>

      <form onSubmit={handleSubmit} className="bg-gray-100 dark:bg-gray-900 rounded-2xl p-6 shadow-lg max-w-xl mb-8 border border-cyan-600">
        <h2 className="text-xl font-semibold mb-4">{form.id === null ? 'Agregar nueva' : 'Editar computadora'}</h2>
        <div className="grid grid-cols-2 gap-4 mb-4">
          {["marca", "cpu", "gpu", "ram", "disco"].map((field) => (
            <input
              key={field}
              placeholder={field.toUpperCase()}
              value={form[field]}
              onChange={(e) => setForm({ ...form, [field]: e.target.value })}
              className="bg-white dark:bg-gray-800 text-black dark:text-white p-2 rounded outline-none border border-gray-300 dark:border-gray-700 focus:border-cyan-500"
            />
          ))}
        </div>
        <button type="submit" className="bg-cyan-600 hover:bg-cyan-700 px-4 py-2 rounded text-white font-semibold transition-all">
          {form.id === null ? 'Agregar' : 'Actualizar'}
        </button>
      </form>

      <button
        onClick={getComputers}
        className="mb-6 bg-cyan-700 hover:bg-cyan-800 px-4 py-2 rounded text-white font-semibold transition-all"
      >
        🔄 Cargar Computadoras
      </button>

      <div className="grid gap-4 max-w-3xl">
        {computers.map((comp, i) => (
          <div key={i} className="bg-gray-100 dark:bg-gray-800 border border-cyan-700 rounded-xl p-4 flex justify-between items-center hover:shadow-cyan-500/20 transition-all">
            <div>
              <p className="text-cyan-700 dark:text-cyan-300 font-semibold text-lg">{comp.marca}</p>
              <p className="text-sm text-gray-600 dark:text-gray-400">CPU: {comp.cpu} | GPU: {comp.gpu} | RAM: {comp.ram} GB | Disco: {comp.disco}</p>
            </div>
            <div className="flex gap-2">
              <button onClick={() => handleEdit(comp)} className="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 rounded font-bold text-black">
                ✏️
              </button>
              <button onClick={() => handleDelete(comp.id)} className="px-3 py-1 bg-red-600 hover:bg-red-700 rounded font-bold text-white">
                🗑️
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

export default App;
