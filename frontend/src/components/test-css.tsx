export function TestCSS() {
  return (
    <div className="p-8 bg-blue-500 text-white rounded-lg">
      <h1 className="text-2xl font-bold mb-4">Test CSS</h1>
      <p className="text-lg">Jika ini terlihat dengan background biru, CSS sudah bekerja!</p>
      <button className="mt-4 px-4 py-2 bg-red-500 hover:bg-red-600 rounded">
        Test Button
      </button>
    </div>
  );
}