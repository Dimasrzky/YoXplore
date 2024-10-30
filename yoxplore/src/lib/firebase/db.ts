import { 
    collection, 
    doc, 
    getDocs, 
    getDoc, 
    addDoc, 
    updateDoc, 
    deleteDoc,
    query,
    where,
    DocumentData
  } from 'firebase/firestore';
  import { db } from './config';
  
  export const getCollection = async (collectionName: string) => {
    try {
      const querySnapshot = await getDocs(collection(db, collectionName));
      return querySnapshot.docs.map(doc => ({
        id: doc.id,
        ...doc.data()
      }));
    } catch (error) {
      throw error;
    }
  };
  
  export const getDocument = async (collectionName: string, id: string) => {
    try {
      const docRef = doc(db, collectionName, id);
      const docSnap = await getDoc(docRef);
      if (docSnap.exists()) {
        return { id: docSnap.id, ...docSnap.data() };
      }
      return null;
    } catch (error) {
      throw error;
    }
  };
  
  export const addDocument = async (collectionName: string, data: DocumentData) => {
    try {
      const docRef = await addDoc(collection(db, collectionName), data);
      return docRef.id;
    } catch (error) {
      throw error;
    }
  };
  
  export const updateDocument = async (collectionName: string, id: string, data: DocumentData) => {
    try {
      const docRef = doc(db, collectionName, id);
      await updateDoc(docRef, data);
    } catch (error) {
      throw error;
    }
  };
  
  export const deleteDocument = async (collectionName: string, id: string) => {
    try {
      const docRef = doc(db, collectionName, id);
      await deleteDoc(docRef);
    } catch (error) {
      throw error;
    }
  };