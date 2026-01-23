# streamlit_app.py
# Lancer : streamlit run streamlit_app.py
#
# Prérequis :
# pip install streamlit pandas numpy matplotlib statsmodels scikit-learn openpyxl pmdarima

import streamlit as st
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt

from statsmodels.tsa.stattools import adfuller, acf, pacf
from statsmodels.tsa.arima.model import ARIMA
from statsmodels.tsa.statespace.sarimax import SARIMAX
from statsmodels.tsa.seasonal import STL
from statsmodels.graphics.tsaplots import plot_acf, plot_pacf

from sklearn.metrics import mean_squared_error

# Auto-ARIMA
import pmdarima as pm


st.set_page_config(
    page_title="Projet — ARIMA / SARIMA / SARIMAX (+ exog) + STL + ADF + ACF/PACF",
    layout="wide",
)

st.title("Projet — Série temporelle (ARIMA / SARIMA / SARIMAX) + STL + ADF + ACF/PACF + Forecast")


# =========================
# Utils robustes
# =========================
def read_data_robust(file):
    """Lecture robuste multi-formats (CSV/Excel/JSON/TXT)."""
    filename = file.name.lower()

    if filename.endswith((".xlsx", ".xls")):
        df = pd.read_excel(file)
        df.columns = df.columns.str.strip()
        return df

    if filename.endswith(".json"):
        df = pd.read_json(file)
        df.columns = df.columns.str.strip()
        return df

    encodings = ["utf-8", "latin1", "cp1252"]
    seps = [None, ";", ",", "\t", "|"]
    last_error = None

    for enc in encodings:
        for sep in seps:
            try:
                file.seek(0)
                df = pd.read_csv(file, sep=sep, engine="python", encoding=enc)

                if df.shape[1] == 1 and any(s in str(df.columns[0]) for s in [";", ",", "\t", "|"]):
                    continue

                df.columns = df.columns.str.strip()
                return df
            except Exception as e:
                last_error = e
                continue

    raise ValueError("Impossible de lire le fichier. Formats : CSV/Excel/JSON/TXT.") from last_error


def infer_date_col(df: pd.DataFrame):
    for c in df.columns:
        cl = c.lower()
        if cl in ("date", "datetime", "timestamp", "time", "month", "ds") or "date" in cl:
            return c

    best, best_ratio = None, 0.0
    for c in df.columns:
        parsed = pd.to_datetime(df[c], errors="coerce")
        ratio = float(parsed.notna().mean())
        if ratio > best_ratio and ratio > 0.6:
            best_ratio = ratio
            best = c
    return best


def infer_value_col(df: pd.DataFrame, date_col: str | None):
    candidates = [c for c in df.columns if c != date_col]
    num = [c for c in candidates if pd.api.types.is_numeric_dtype(df[c])]
    if num:
        return max(num, key=lambda c: float(df[c].var(skipna=True)))
    return None


def adf_test(series: pd.Series):
    s = pd.to_numeric(series, errors="coerce").dropna()
    stat, pval, lags, nobs, *_ = adfuller(s, autolag="AIC")
    return {"adf_stat": float(stat), "p_value": float(pval), "lags": int(lags), "nobs": int(nobs)}


def rmse(y_true, y_pred):
    return float(np.sqrt(mean_squared_error(y_true, y_pred)))


def safe_int(x, default):
    try:
        return int(x)
    except Exception:
        return default


def build_time_indexed_df(df: pd.DataFrame, date_col: str):
    tmp = df.copy()
    tmp[date_col] = pd.to_datetime(tmp[date_col], errors="coerce")
    tmp = tmp.dropna(subset=[date_col]).sort_values(date_col)
    tmp = tmp.set_index(date_col)
    tmp = tmp[~tmp.index.duplicated(keep="last")]
    return tmp


def aggregate_if_needed(df_idx: pd.DataFrame, agg: str):
    if df_idx.index.has_duplicates:
        if agg == "sum":
            return df_idx.groupby(df_idx.index).sum(numeric_only=True)
        if agg == "median":
            return df_idx.groupby(df_idx.index).median(numeric_only=True)
        if agg == "last":
            return df_idx.groupby(df_idx.index).last()
        return df_idx.groupby(df_idx.index).mean(numeric_only=True)
    return df_idx


def force_freq(s: pd.Series, freq: str):
    if freq == "Auto":
        return s
    s2 = s.asfreq(freq)
    s2 = s2.interpolate(limit_direction="both")
    return s2


def stl_decompose(series: pd.Series, period: int, robust: bool):
    s = series.copy().interpolate(limit_direction="both")
    period = max(2, int(period))
    return STL(s, period=period, robust=robust).fit()


# =========================
# Plots
# =========================
def plot_simple_series(series: pd.Series, title: str, ylabel: str):
    fig, ax = plt.subplots(figsize=(11, 4))
    series.plot(ax=ax)
    ax.set_title(title)
    ax.set_xlabel("Date")
    ax.set_ylabel(ylabel)
    ax.grid(True, alpha=0.3)
    st.pyplot(fig, clear_figure=True)


def plot_forecast_overlay(history: pd.Series, forecast: pd.Series, title: str, ylabel: str, zoom_last: int = 120):
    fig, ax = plt.subplots(figsize=(11, 4))
    hist_plot = history.iloc[-zoom_last:] if (zoom_last and len(history) > zoom_last) else history
    hist_plot.plot(ax=ax, label="Observé")
    forecast.plot(ax=ax, label="Prévision", linestyle="--")
    ax.set_title(title)
    ax.set_xlabel("Date")
    ax.set_ylabel(ylabel)
    ax.grid(True, alpha=0.3)
    ax.legend()
    st.pyplot(fig, clear_figure=True)

def plot_test_observed_pred(y_te: pd.Series, pred: pd.Series, title: str, ylabel: str):
    fig, ax = plt.subplots(figsize=(11, 4))
    y_te.plot(ax=ax, label="Observé (test)")
    pred.plot(ax=ax, label="Prédit (test)", linestyle="--")
    ax.set_title(title)
    ax.set_xlabel("Date")
    ax.set_ylabel(ylabel)
    ax.grid(True, alpha=0.3)
    ax.legend()
    st.pyplot(fig, clear_figure=True)


def plot_acf_pacf_block(s: pd.Series, title_prefix: str, max_lags_user: int):
    s = pd.to_numeric(s, errors="coerce").dropna()
    n = len(s)
    if n < 20:
        st.warning("Série trop courte pour ACF/PACF (>= ~20 points).")
        return

    max_lags_user = int(max_lags_user)
    max_lags_acf = max(5, min(max_lags_user, n - 1))
    max_lags_pacf = max(5, min(max_lags_user, (n // 2) - 1))

    c1, c2 = st.columns(2)

    with c1:
        fig1, ax1 = plt.subplots(figsize=(6, 3))
        plot_acf(s, lags=max_lags_acf, ax=ax1)
        ax1.set_title(f"{title_prefix} — ACF (lags={max_lags_acf})")
        ax1.grid(True, alpha=0.3)
        st.pyplot(fig1, clear_figure=True)

    with c2:
        fig2, ax2 = plt.subplots(figsize=(6, 3))
        plot_pacf(s, lags=max_lags_pacf, ax=ax2, method="ywm")
        ax2.set_title(f"{title_prefix} — PACF (lags={max_lags_pacf})")
        ax2.grid(True, alpha=0.3)
        st.pyplot(fig2, clear_figure=True)


# =========================
# Heuristiques (p,d,q,P,D,Q)
# =========================
def suggest_d_by_adf(y: pd.Series, max_d: int = 2):
    y0 = pd.to_numeric(y, errors="coerce").dropna()
    best = {"d": 0, "p_value": None}

    for d in range(0, max_d + 1):
        yd = y0.diff(d).dropna() if d > 0 else y0
        if len(yd) < 20:
            continue
        pval = adf_test(yd)["p_value"]
        if best["p_value"] is None or pval < best["p_value"]:
            best = {"d": d, "p_value": pval}
        if pval < 0.05:
            return {"d": d, "p_value": pval, "rule": "ADF<0.05 (stationnaire)"}

    return {"d": best["d"], "p_value": best["p_value"], "rule": "Meilleure tentative (ADF pas <0.05)"}


def suggest_D_by_adf(y: pd.Series, s: int):
    y0 = pd.to_numeric(y, errors="coerce").dropna()
    if s is None or s < 2 or len(y0) < 3 * s:
        return {"D": 0, "p_value": None, "rule": "Saison non fiable / série trop courte"}

    p0 = adf_test(y0)["p_value"]

    y1 = y0.diff(int(s)).dropna()
    if len(y1) < 20:
        return {"D": 0, "p_value": p0, "rule": "Diff saisonnière trop agressive"}

    p1 = adf_test(y1)["p_value"]

    if p1 < p0:
        return {"D": 1, "p_value": p1, "rule": "ADF améliorée avec diff saisonnière"}
    return {"D": 0, "p_value": p0, "rule": "ADF meilleure sans diff saisonnière"}


def _count_initial_significant_lags(values, conf, max_lags=10, min_run_stop=2):
    count = 0
    non_sig_run = 0
    for k in range(1, min(max_lags, len(values) - 1) + 1):
        if abs(values[k]) > conf:
            count = k
            non_sig_run = 0
        else:
            non_sig_run += 1
            if non_sig_run >= min_run_stop:
                break
    return count


def suggest_pq_from_acf_pacf(y: pd.Series, max_lags: int = 20):
    y0 = pd.to_numeric(y, errors="coerce").dropna()
    n = len(y0)
    if n < 30:
        return {"p": 1, "q": 1, "conf": None, "rule": "Série courte → fallback"}

    conf = 1.96 / np.sqrt(n)
    acf_vals = acf(y0, nlags=max_lags, fft=True)
    pacf_vals = pacf(y0, nlags=max_lags, method="ywm")

    q = _count_initial_significant_lags(acf_vals, conf, max_lags=max_lags)
    p = _count_initial_significant_lags(pacf_vals, conf, max_lags=max_lags)

    return {"p": int(p), "q": int(q), "conf": float(conf), "rule": "Seuil ±1.96/sqrt(N)"}


def suggest_PQ_seasonal(y: pd.Series, s: int, max_mult: int = 3):
    y0 = pd.to_numeric(y, errors="coerce").dropna()
    n = len(y0)
    if s is None or s < 2 or n < 3 * s:
        return {"P": 0, "Q": 0, "conf": None, "rule": "Série trop courte pour saisonnier"}

    conf = 1.96 / np.sqrt(n)
    max_lags = min(int(max_mult * s), n - 1)

    acf_vals = acf(y0, nlags=max_lags, fft=True)
    pacf_vals = pacf(y0, nlags=max_lags, method="ywm")

    P = 0
    Q = 0
    for m in range(1, max_mult + 1):
        lag = m * int(s)
        if lag <= max_lags:
            if abs(pacf_vals[lag]) > conf:
                P = m
            if abs(acf_vals[lag]) > conf:
                Q = m

    return {"P": int(P), "Q": int(Q), "conf": float(conf), "rule": f"Pics à lag s,2s,3s (s={s})"}


# =========================
# Upload
# =========================
uploaded = st.file_uploader("1) Charger un fichier (CSV / Excel)", type=["csv", "xlsx", "xls", "json", "txt"])
use_sample = st.checkbox("Utiliser un dataset exemple (si rien upload)", value=(uploaded is None))

if use_sample:
    rng = pd.date_range("2022-01-01", "2024-12-31", freq="D")
    t = np.arange(len(rng))
    np.random.seed(42)
    df = pd.DataFrame(
        {
            "Date": rng,
            "CO(GT)": 2
            + 0.002 * t
            + 0.6 * np.sin(2 * np.pi * t / 7)
            + 0.3 * np.sin(2 * np.pi * t / 365.25)
            + 0.2 * np.random.randn(len(rng)),
            "T": 15 + 5 * np.sin(2 * np.pi * t / 365.25) + 0.8 * np.random.randn(len(rng)),
            "RH": 55 + 10 * np.sin(2 * np.pi * t / 30) + 2 * np.random.randn(len(rng)),
        }
    )
else:
    if uploaded is None:
        st.stop()
    df = read_data_robust(uploaded)

df.columns = df.columns.str.strip()

with st.expander("Aperçu du dataset", expanded=True):
    st.dataframe(df.head(30), use_container_width=True)
    st.caption(f"{df.shape[0]} lignes × {df.shape[1]} colonnes")


# =========================
# Choix colonnes + freq
# =========================
date_guess = infer_date_col(df)
value_guess = infer_value_col(df, date_guess)

c1, c2, c3 = st.columns([1.3, 1.3, 1.4])
with c1:
    date_col = st.selectbox(
        "2) Colonne date",
        options=df.columns.tolist(),
        index=(df.columns.get_loc(date_guess) if date_guess in df.columns else 0),
    )

with c2:
    value_options = [c for c in df.columns.tolist() if c != date_col]
    if not value_options:
        st.error("Aucune colonne valeur disponible (problème parsing).")
        st.stop()
    default_idx = value_options.index(value_guess) if value_guess in value_options else 0
    target_col = st.selectbox("3) Colonne cible (à prédire)", options=value_options, index=default_idx)

with c3:
    freq = st.selectbox("4) Fréquence (optionnel)", options=["Auto", "D", "W-SUN", "MS", "M"], index=0)
    st.caption("Pour AirQuality: souvent journalier → D")


# =========================
# Préparation série + exog
# =========================
df_idx = build_time_indexed_df(df, date_col)

agg = st.selectbox("Agrégation si doublons de dates", options=["mean", "sum", "median", "last"], index=0)
df_idx = aggregate_if_needed(df_idx, agg)
df_idx = df_idx.reindex(df_idx.index.sort_values())

y = pd.to_numeric(df_idx[target_col], errors="coerce").dropna()
y = force_freq(y, freq)

df_idx = df_idx.reindex(y.index)

for c in df_idx.columns:
    df_idx[c] = pd.to_numeric(df_idx[c], errors="ignore")

num_cols = df_idx.select_dtypes(include=["number"]).columns
if len(num_cols) > 0:
    df_idx[num_cols] = df_idx[num_cols].interpolate(limit_direction="both")

non_num_cols = [c for c in df_idx.columns if c not in num_cols]
if len(non_num_cols) > 0:
    df_idx[non_num_cols] = df_idx[non_num_cols].ffill().bfill()

if len(y) < 30:
    st.error("Série trop courte après nettoyage (>= ~30 points).")
    st.stop()


# =========================
# I) Série originale
# =========================
st.subheader("I) Série originale")
plot_simple_series(y, "Série temporelle originale", ylabel=target_col)


# =========================
# II) STL
# =========================
st.subheader("II) Décomposition STL (trend / seasonal / resid)")
default_period = 7 if freq in ("Auto", "D") else 12
p1, p2 = st.columns([1, 2])
with p1:
    stl_period = st.number_input("Période STL", min_value=2, max_value=365, value=int(default_period), step=1)
with p2:
    st.caption("Journalier: 7 (hebdo) ou 365 (annuel). Mensuel: 12.")

stl_robust = st.checkbox("STL robust (si outliers)", value=True)

try:
    stl_res = stl_decompose(y, period=int(stl_period), robust=stl_robust)
    fig, axes = plt.subplots(4, 1, figsize=(11, 8), sharex=True)
    axes[0].plot(stl_res.observed)
    axes[0].set_title("Observed")
    axes[1].plot(stl_res.trend)
    axes[1].set_title("Trend")
    axes[2].plot(stl_res.seasonal)
    axes[2].set_title("Seasonal")
    axes[3].plot(stl_res.resid)
    axes[3].set_title("Resid")
    for ax in axes:
        ax.grid(True, alpha=0.3)
    st.pyplot(fig, clear_figure=True)
except Exception as e:
    st.warning(f"STL impossible (période/série). Erreur: {e}")


# =========================
# III) ADF
# =========================
st.subheader("III) Stationnarité — Test ADF (Augmented Dickey-Fuller)")
adf = adf_test(y)
st.write(
    f"ADF = **{adf['adf_stat']:.4f}** | p-value = **{adf['p_value']:.4g}** | lags = {adf['lags']} | nobs = {adf['nobs']}"
)
if adf["p_value"] < 0.05:
    st.success("✅ Série stationnaire (5%).")
else:
    st.warning("⚠️ Série NON stationnaire (5%). ARIMA/SARIMA recommandé (différenciation d et/ou saisonnalité).")


# =========================
# IV) ACF / PACF
# =========================
st.subheader("IV) ACF / PACF — aide au choix de p, q (et P, Q en saisonnier)")
max_lags_user = st.number_input("Nombre de lags (max)", min_value=5, max_value=200, value=40, step=5)

st.info(
    "💡 Règles rapides :\n"
    "- **PACF** → aide à choisir **p** (coupure nette après p)\n"
    "- **ACF** → aide à choisir **q** (coupure nette après q)\n"
    "- Pour saisonnier (SARIMA), regarde les pics à **lag = s, 2s, 3s** :\n"
    "  - Pics dans **PACF** à s → plutôt **P**\n"
    "  - Pics dans **ACF** à s → plutôt **Q**\n"
    "- Si la série n’est pas stationnaire, regarde ACF/PACF sur la **série différenciée** (d et/ou D)."
)

plot_acf_pacf_block(y, "Série originale", max_lags_user)

with st.expander("Option : ACF/PACF sur série transformée (log + d + D saisonnier)", expanded=False):
    use_log = st.checkbox("Appliquer log(1+x)", value=False)
    d_diag = st.slider("Différenciation d", 0, 2, 1)
    use_seas = st.checkbox("Diff saisonnière D", value=False)
    s_diag = st.number_input("Période saisonnière s (pour D)", min_value=2, max_value=365, value=int(stl_period), step=1)

    tr = y.copy()
    if use_log:
        tr = np.log1p(tr)
    if d_diag > 0:
        tr = tr.diff(int(d_diag))
    if use_seas:
        tr = tr.diff(int(s_diag))

    tr = tr.dropna()
    if len(tr) > 20:
        adf2 = adf_test(tr)
        st.write(f"ADF après transfos: ADF={adf2['adf_stat']:.4f} | p-value={adf2['p_value']:.4g}")
        plot_simple_series(tr, "Série transformée (diagnostic)", ylabel=f"{target_col} (transfo)")
        plot_acf_pacf_block(tr, "Série transformée", max_lags_user)
    else:
        st.warning("Transformation trop agressive → série trop courte.")


# =========================
# IV-bis) Suggestions automatiques p,d,q,P,D,Q (version académique)
# =========================
st.subheader("IV-bis) Suggestions automatiques des ordres (version académique)")

sd_raw = suggest_d_by_adf(y, max_d=2)
spq_raw = suggest_pq_from_acf_pacf(y, max_lags=int(max_lags_user))

suggest_s = int(stl_period)
sD_raw = suggest_D_by_adf(y, s=suggest_s)
sPQ_raw = suggest_PQ_seasonal(y, s=suggest_s, max_mult=3)

st.info(
    "📌 Lecture correcte : les suggestions automatiques sont **indicatives**. "
    "Si l'ACF/PACF ne présente pas une coupure nette, p et q peuvent sortir élevés. "
    "En pratique (et en examen), on applique la **parcimonie** : on garde des ordres faibles et stables."
)

cap_col1, cap_col2, cap_col3 = st.columns([1, 1, 1])
with cap_col1:
    cap_pq = st.slider("Cap p/q (parsimonie)", min_value=1, max_value=10, value=3, step=1)
with cap_col2:
    cap_PQ = st.slider("Cap P/Q (parsimonie)", min_value=0, max_value=5, value=2, step=1)
with cap_col3:
    cap_dD = st.slider("Cap d/D (parsimonie)", min_value=0, max_value=2, value=1, step=1)

sd = {"d": int(min(sd_raw["d"], cap_dD)), "p_value": sd_raw["p_value"], "rule": sd_raw["rule"]}
spq = {"p": int(min(spq_raw["p"], cap_pq)), "q": int(min(spq_raw["q"], cap_pq)), "conf": spq_raw["conf"], "rule": spq_raw["rule"]}
sD = {"D": int(min(sD_raw["D"], cap_dD)), "p_value": sD_raw["p_value"], "rule": sD_raw["rule"]}
sPQ = {"P": int(min(sPQ_raw["P"], cap_PQ)), "Q": int(min(sPQ_raw["Q"], cap_PQ)), "conf": sPQ_raw["conf"], "rule": sPQ_raw["rule"]}

st.warning(
    "⚠️ À rendre / à dire : on présente d'abord les ACF/PACF (diagnostic), "
    "puis on propose un modèle **parcimonieux** (ordres faibles) pour éviter le sur-ajustement."
)

st.table(
    pd.DataFrame(
        {
            "Paramètre": ["d", "p", "q", "D", "P", "Q", "s"],
            "Suggestion brute": [sd_raw["d"], spq_raw["p"], spq_raw["q"], sD_raw["D"], sPQ_raw["P"], sPQ_raw["Q"], suggest_s],
            "Valeur retenue (parsimonie)": [sd["d"], spq["p"], spq["q"], sD["D"], sPQ["P"], sPQ["Q"], suggest_s],
            "Justification": [sd_raw["rule"], "PACF (coupure initiale)", "ACF (coupure initiale)", sD_raw["rule"], "PACF aux multiples de s", "ACF aux multiples de s", "Période STL"],
        }
    )
)


# =========================
# V) Modélisation + paramètres
# =========================
st.subheader("V) Modélisation — ARIMA ou SARIMA (SARIMAX)")
model_type = st.radio("Choisir le modèle", ["ARIMA", "SARIMA (via SARIMAX)"], horizontal=True)

cA, cB, cC, cD = st.columns(4)
with cA:
    p = safe_int(st.text_input("p", str(spq["p"])), 1)
with cB:
    d = safe_int(st.text_input("d", str(sd["d"])), 1)
with cC:
    q = safe_int(st.text_input("q", str(spq["q"])), 1)
with cD:
    horizon = safe_int(st.text_input("Horizon de prédiction (pas futurs)", "30"), 30)

if model_type == "SARIMA (via SARIMAX)":
    cS1, cS2, cS3, cS4 = st.columns(4)
    with cS1:
        P = safe_int(st.text_input("P (saisonnier)", str(sPQ["P"])), 1)
    with cS2:
        D = safe_int(st.text_input("D (saisonnier)", str(sD["D"])), 1)
    with cS3:
        Q = safe_int(st.text_input("Q (saisonnier)", str(sPQ["Q"])), 1)
    with cS4:
        s = safe_int(st.text_input("s (période saisonnière)", str(suggest_s)), suggest_s)

    st.markdown("### Variables exogènes (optionnel, SARIMAX)")
    candidate_exog = [c for c in df_idx.columns if c != target_col]
    numeric_exog = [c for c in candidate_exog if pd.api.types.is_numeric_dtype(df_idx[c])]

    if numeric_exog:
        exog_cols = st.multiselect("Choisir une ou plusieurs variables exogènes", options=numeric_exog, default=[])
    else:
        exog_cols = []
        st.info("Aucune colonne numérique exogène détectée.")

    exog_mode = st.selectbox(
        "Si exog choisi : comment gérer l’exog futur ?",
        options=["Répéter la dernière valeur", "Mettre à 0", "Moyenne historique"],
        index=0
    )
else:
    P = D = Q = s = None
    exog_cols = []
    exog_mode = None


# =========================
# Session state (mémoire entre reruns)
# =========================
if "manual_done" not in st.session_state:
    st.session_state.manual_done = False
if "manual_bundle" not in st.session_state:
    st.session_state.manual_bundle = None
if "auto_bundle" not in st.session_state:
    st.session_state.auto_bundle = None

# =========================
# VI) Entraîner + prédire
# =========================
run = st.button("🚀 Entraîner et prédire")

if run:
    try:
        y_train = y.copy().dropna()
        exog_train = None
        exog_future = None

        if model_type == "SARIMA (via SARIMAX)" and exog_cols:
            X = df_idx[exog_cols].copy()
            for c in exog_cols:
                X[c] = pd.to_numeric(X[c], errors="coerce")
            X = X.interpolate(limit_direction="both").reindex(y_train.index).interpolate(limit_direction="both")
            exog_train = X

            last_row = X.iloc[-1]
            if exog_mode == "Répéter la dernière valeur":
                Xf = pd.DataFrame([last_row.values] * horizon, columns=exog_cols)
            elif exog_mode == "Mettre à 0":
                Xf = pd.DataFrame(np.zeros((horizon, len(exog_cols))), columns=exog_cols)
            else:
                mu = X.mean(axis=0)
                Xf = pd.DataFrame([mu.values] * horizon, columns=exog_cols)
            exog_future = Xf

        if model_type == "ARIMA":
            model = ARIMA(y_train, order=(p, d, q), trend="n")
            res = model.fit()
            y_fore = res.get_forecast(steps=horizon).predicted_mean
            title = f"ARIMA({p},{d},{q}) — horizon={horizon}"
        else:
            model = SARIMAX(
                y_train,
                order=(p, d, q),
                seasonal_order=(P, D, Q, s),
                exog=exog_train,
                enforce_stationarity=False,
                enforce_invertibility=False,
            )
            res = model.fit(disp=False)
            y_fore = (
                res.get_forecast(steps=horizon, exog=exog_future).predicted_mean
                if exog_cols
                else res.get_forecast(steps=horizon).predicted_mean
            )
            title = f"SARIMA/SARIMAX({p},{d},{q})({P},{D},{Q},{s}) — horizon={horizon}"

        last_idx = y_train.index[-1]
        inferred = pd.infer_freq(y_train.index)
        used_freq = inferred if inferred is not None else (None if freq == "Auto" else freq)

        future_index = (
            pd.RangeIndex(start=0, stop=horizon, step=1)
            if used_freq is None
            else pd.date_range(start=last_idx, periods=horizon + 1, freq=used_freq)[1:]
        )
        y_fore = pd.Series(np.asarray(y_fore), index=future_index, name="forecast")

        st.success("✅ Modèle entraîné + prévision générée")
        with st.expander("Résumé modèle (summary)"):
            st.text(res.summary().as_text())

        st.subheader("VII) Prédiction — graphique + comparaison observé/prédit")
        plot_forecast_overlay(y_train, y_fore, title=title, ylabel=target_col, zoom_last=120)
        
                # --- Sauvegarde pour ne pas perdre le modèle manuel au rerun
        st.session_state.manual_done = True
        st.session_state.manual_bundle = {
            "model_type": model_type,
            "title": title,
            "target_col": target_col,
            "y_train": y_train,
            "y_fore": y_fore,
            "res_summary": res.summary().as_text(),
            "p": p, "d": d, "q": q,
            "P": P, "D": D, "Q": Q, "s": s,
            "stl_period": int(stl_period),
            "exog_cols": exog_cols,
            "exog_train": exog_train,
        }
        st.session_state.auto_bundle = None  # reset auto si on relance le manuel


     

    

    except Exception as e:
        st.error(f"Erreur modèle : {e}")
        st.info("Astuce : réduis p/q/P/Q, ajuste s (souvent 7 si journalier), et vérifie les exog.")


# ============================================================
# Affichage persistant du modèle manuel (après rerun)
# ============================================================
if st.session_state.get("manual_done", False) and st.session_state.get("manual_bundle") is not None:
    b = st.session_state.manual_bundle

    st.subheader("VII) Résultats modèle manuel ")
    with st.expander("Résumé modèle (summary) — manuel", expanded=False):
        st.text(b["res_summary"])

    plot_forecast_overlay(
        b["y_train"],
        b["y_fore"],
        title=b["title"],
        ylabel=b["target_col"],
        zoom_last=120
    )


# ============================================================
# VII-bis) Auto-ARIMA / Auto-SARIMA — comparaison RMSE (sur test)
# (Ne relance pas le manuel : on utilise session_state)
# ============================================================
st.subheader("VII-bis) Auto-ARIMA / Auto-SARIMA — comparaison RMSE (sur période test)")

if not st.session_state.manual_done or st.session_state.manual_bundle is None:
    st.info("Exécute d'abord le modèle manuel (bouton 🚀 Entraîner et prédire).")
else:
    b = st.session_state.manual_bundle

    # Contrôles auto (hors du bouton manuel => OK au rerun)
    test_pct = st.slider("Taille du test (%)", 10, 40, 20, 5)
    seasonal_auto = st.checkbox("Auto avec saisonnalité (Auto-SARIMA)", value=True)
    m_auto = st.number_input(
        "Période saisonnière m (auto)",
        min_value=1,
        max_value=365,
        value=int(b["stl_period"]),
        step=1
    )

    run_auto_btn = st.button("⚙️ Lancer Auto-ARIMA/SARIMA + comparer RMSE")

    if run_auto_btn:
        y_train = b["y_train"]
        n_test = max(1, int(len(y_train) * test_pct / 100))
        y_tr = y_train.iloc[:-n_test]
        y_te = y_train.iloc[-n_test:]

        if len(y_tr) < 30 or len(y_te) < 5:
            st.warning("Série trop courte pour split train/test fiable. Diminue le % test.")
        else:
            # --- RMSE modèle A (manuel) sur test + stockage des prédictions
            rmse_A = None
            predA_series = None

            try:
                if b["model_type"] == "ARIMA":
                    modelA_eval = ARIMA(y_tr, order=(b["p"], b["d"], b["q"]), trend="n")
                    resA_eval = modelA_eval.fit()
                    predA = resA_eval.get_forecast(steps=len(y_te)).predicted_mean
                else:
                    exog_train = b["exog_train"]
                    ex_tr = exog_train.loc[y_tr.index] if exog_train is not None else None
                    ex_te = exog_train.loc[y_te.index] if exog_train is not None else None

                    modelA_eval = SARIMAX(
                        y_tr,
                        order=(b["p"], b["d"], b["q"]),
                        seasonal_order=(b["P"], b["D"], b["Q"], b["s"]),
                        exog=ex_tr,
                        enforce_stationarity=False,
                        enforce_invertibility=False,
                    )
                    resA_eval = modelA_eval.fit(disp=False)
                    predA = (
                        resA_eval.get_forecast(steps=len(y_te), exog=ex_te).predicted_mean
                        if ex_te is not None
                        else resA_eval.get_forecast(steps=len(y_te)).predicted_mean
                    )

                # ✅ IMPORTANT : re-indexer la prédiction sur l'index de y_te
                predA_series = pd.Series(np.asarray(predA), index=y_te.index, name="pred_manual_test")
                rmse_A = rmse(y_te.values, predA_series.values)

            except Exception as e:
                st.error(f"Évaluation RMSE du modèle manuel impossible : {e}")

            # --- Auto-ARIMA (sans exog) sur test + stockage des prédictions
            rmse_B = None
            predB_series = None
            auto_order = None
            auto_seasonal = None

            try:
                auto_model = pm.auto_arima(
                    y_tr,
                    seasonal=seasonal_auto,
                    m=int(m_auto) if seasonal_auto else 1,
                    stepwise=True,
                    suppress_warnings=True,
                    error_action="ignore",
                    trace=False,
                    max_p=5, max_q=5, max_P=2, max_Q=2,
                    max_d=2, max_D=1
                )

                predB = auto_model.predict(n_periods=len(y_te))

                # ✅ IMPORTANT : re-indexer la prédiction auto sur l'index de y_te
                predB_series = pd.Series(np.asarray(predB), index=y_te.index, name="pred_auto_test")

                rmse_B = rmse(y_te.values, predB_series.values)
                auto_order = auto_model.order
                auto_seasonal = auto_model.seasonal_order if seasonal_auto else None

            except Exception as e:
                st.warning(f"Auto-ARIMA/SARIMA a échoué : {e}")

            # --- Stocker résultat auto (persiste aux reruns) + séries pour tracés
            st.session_state.auto_bundle = {
                "rmse_A": rmse_A,
                "rmse_B": rmse_B,
                "auto_order": auto_order,
                "auto_seasonal": auto_seasonal,
                "test_pct": test_pct,
                "seasonal_auto": seasonal_auto,
                "m_auto": int(m_auto),

                # ✅ Séries stockées pour afficher les graphes Observé + Prédit
                "y_te": y_te,
                "predA": predA_series,
                "predB": predB_series,
            }

    # --- Affichage résultat si déjà calculé
    if st.session_state.auto_bundle is not None:
        ab = st.session_state.auto_bundle
        rows = []

        if ab["rmse_A"] is not None:
            rows.append({
                "Modèle": f"A — Manuel ({b['model_type']})",
                "Ordres": f"({b['p']},{b['d']},{b['q']})" if b["model_type"] == "ARIMA"
                          else f"({b['p']},{b['d']},{b['q']})({b['P']},{b['D']},{b['Q']},{b['s']})",
                "Exog": "Oui" if (b["model_type"] != "ARIMA" and len(b["exog_cols"]) > 0) else "Non",
                "RMSE (test)": ab["rmse_A"]
            })

        if ab["rmse_B"] is not None:
            rows.append({
                "Modèle": "B — Auto-ARIMA/SARIMA (pmdarima)",
                "Ordres": f"order={ab['auto_order']}, seasonal={ab['auto_seasonal']}",
                "Exog": "Non (auto_arima)",
                "RMSE (test)": ab["rmse_B"]
            })

        if rows:
            st.dataframe(pd.DataFrame(rows), use_container_width=True)

        if (ab["rmse_A"] is not None) and (ab["rmse_B"] is not None):
            if ab["rmse_B"] < ab["rmse_A"]:
                st.success("✅ Conclusion : Auto-ARIMA/SARIMA est meilleur (RMSE test plus faible).")
            elif ab["rmse_B"] > ab["rmse_A"]:
                st.success("✅ Conclusion : Le modèle manuel est meilleur (RMSE test plus faible).")
            else:
                st.info("ℹ️ Conclusion : égalité (RMSE identique).")
