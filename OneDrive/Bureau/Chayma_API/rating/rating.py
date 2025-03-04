from flask import Flask, request, jsonify
import joblib
import numpy as np
import re
from nltk.corpus import stopwords
from nltk.stem.porter import PorterStemmer
import nltk

# Télécharger les stopwords nécessaires
nltk.download('stopwords')
stop_words = set(stopwords.words('english'))
ps = PorterStemmer()

# Charger le modèle et le vectoriseur
try:
    model = joblib.load('xgboost_model.pkl')
    vectorizer = joblib.load('tfidf_vectorizer.pkl')
except FileNotFoundError as e:
    raise SystemExit(f"Erreur : {e}. Assurez-vous que les fichiers nécessaires existent.")

# Fonction pour nettoyer le texte
def clean_review(review):
    review = re.sub('[^a-zA-Z]', ' ', str(review))
    review = review.lower().split()
    review = [ps.stem(word) for word in review if word not in stop_words]
    return ' '.join(review)

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    if not data or 'review' not in data or 'fidality' not in data:
        return jsonify({'error': 'Les données d’entrée sont manquantes ou invalides.'}), 400

    # Extraction et validation des données
    review = data['review']
    try:
        fidality = float(data['fidality'])
    except ValueError:
        return jsonify({'error': 'La fidélité doit être un nombre valide.'}), 400
    
    if not (0 <= fidality <= 1):
        return jsonify({'error': 'La fidélité doit être comprise entre 0 et 1.'}), 400

    # Nettoyage et vectorisation de l'avis client
    cleaned_review = clean_review(review)
    review_vectorized = vectorizer.transform([cleaned_review]).toarray()

    # Ajout de la fidélité comme une caractéristique supplémentaire
    fidality_feature = np.array([fidality]).reshape(1, 1)
    X_input = np.hstack([review_vectorized, fidality_feature])

    # Prédiction
    try:
        prediction = model.predict(X_input)
        prediction_clipped = np.clip(prediction[0], 0, 5)
        return jsonify({
            'review': review,
            'fidality': fidality,
            'predicted_rating': round(prediction_clipped, 2)
        })
    except Exception as e:
        return jsonify({'error': f"Erreur lors de la prédiction : {str(e)}"}), 500

if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000, debug=True)
