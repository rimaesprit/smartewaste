# rating.py - Flask API pour la prédiction de la note
from flask import Flask, request, jsonify

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    try:
        # Extraction des données envoyées dans le JSON
        data = request.json
        review = data.get("review", "")
        fidality = data.get("fidality", 0)

        # Vérifier si la review est vide
        if not review:
            return jsonify({"error": "Review is missing"}), 400

        # Simulation de prédiction de la note
        # Plus la review est longue, plus la note sera élevée (entre 0 et 5)
        predicted_rating = min(5, max(0, len(review) / 10 + fidality * 2))

        return jsonify({"predicted_rating": predicted_rating})
    
    except Exception as e:
        print(f"Erreur serveur: {e}")
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5001, debug=True)
